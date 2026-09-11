<?php

/*
 * Catatan pembelajaran
 * Controller ini merupakan inti sistem parkir. Di sini terdapat proses kendaraan masuk, kendaraan keluar, pencarian aktif, dan pembuatan preview tiket PDF untuk pembelajaran alur aplikasi.
 * Prinsip umum: request -> validasi -> model -> response.
 */

namespace App\Http\Controllers;

use App\Models\AreaParkir;
use App\Models\JenisPelanggan;
use App\Models\Kendaraan;
use App\Models\Log;
use App\Models\Setting;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorPNG;

class ParkirController extends Controller
{
    /**
     * Menampilkan form kendaraan masuk.
     *
     * Fungsi ini bertugas menyiapkan data yang dibutuhkan untuk halaman masuk parkir,
     * seperti daftar area parkir, daftar jenis pelanggan, dan kendaraan yang sudah
     * terdaftar. Data ini dipakai supaya operator tidak perlu mengetik ulang data yang
     * sering dipakai dan supaya form lebih cepat dipahami.
     *
     * @return View halaman masuk parkir dengan data area, jenis pelanggan, dan kendaraan.
     */
    public function masuk(): View
    {
        $areas = AreaParkir::with('tarif')->orderBy('nama')->get();
        $jenisPelanggan = JenisPelanggan::orderBy('nama')->get();
        $registeredVehicles = Kendaraan::with('jenisPelanggan')->get()->map(function ($kendaraan) {
            return [
                'plat_nomor' => strtoupper(trim((string) $kendaraan->plat_nomor)),
                'jenis_kendaraan' => $kendaraan->jenis_kendaraan,
                'jenis_pelanggan' => $kendaraan->jenisPelanggan?->nama ?? 'Reguler',
            ];
        });

        return view('parkir.masuk', compact('areas', 'jenisPelanggan', 'registeredVehicles'));
    }

    /**
     * Menampilkan form kendaraan keluar.
     *
     * Form tidak langsung menyimpan transaksi. JavaScript mengirim data ke
     * previewKeluar() terlebih dahulu agar operator dapat memeriksa total
     * pembayaran sebelum menekan tombol proses final.
     */
    public function keluar(): View
    {
        // Halaman keluar hanya membutuhkan form; rincian transaksi dimuat
        // melalui endpoint preview setelah operator mengirim nomor kendaraan.
        return view('parkir.keluar');
    }

    /**
     * Menghitung rincian kendaraan keluar tanpa menyimpan perubahan.
     *
     * Data dari endpoint ini dipakai oleh modal konfirmasi. Transaksi baru
     * ditutup ketika operator menekan tombol Proses pada modal tersebut.
     *
     * Alur fungsi:
     * 1. Validasi identitas kendaraan dan pilihan karcis hilang.
     * 2. Cari transaksi yang masih berstatus masuk.
     * 3. Hitung durasi, tarif, denda, dan total pembayaran.
     * 4. Kembalikan data JSON untuk ditampilkan di modal.
     *
     * Tidak ada update atau insert database di fungsi ini agar operator masih
     * dapat membatalkan proses tanpa mengubah transaksi aktif.
     */
    public function previewKeluar(Request $request): JsonResponse
    {
        // Validasi input dasar agar pencarian transaksi hanya menerima data
        // dengan format dan panjang yang sesuai dengan struktur database.
        $validated = $request->validate([
            'plat_nomor' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-\s]+$/'],
            'nomor_karcis' => ['nullable', 'string', 'max:50'],
            'karcis_hilang' => ['nullable', 'boolean'],
        ]);

        // Ambil nomor polisi dan nomor karcis. Salah satu saja sudah cukup
        // untuk mencari transaksi aktif.
        $identifier = trim((string) ($validated['plat_nomor'] ?? ''));
        $nomorKarcis = trim((string) ($validated['nomor_karcis'] ?? ''));

        // Tolak request tanpa identitas kendaraan agar query tidak mencari
        // data kosong dan tidak mengembalikan transaksi yang keliru.
        if ($identifier === '' && $nomorKarcis === '') {
            throw ValidationException::withMessages([
                'plat_nomor' => 'Masukkan nomor polisi atau nomor karcis untuk memproses kendaraan keluar.',
            ]);
        }

        // Jika nomor polisi berisi pola nomor karcis, perlakukan sebagai
        // nomor karcis agar operator tidak perlu memilih field berbeda.
        if ($nomorKarcis === '' && str_contains(strtoupper($identifier), 'KRC-')) {
            $nomorKarcis = strtoupper($identifier);
            $identifier = '';
        }

        // Batasi pencarian hanya pada transaksi yang masih berada di area
        // parkir. Eager loading menyiapkan data relasi untuk response modal
        // tanpa query tambahan saat properti relasi dibaca.
        $query = Transaksi::query()
            ->with(['kendaraan', 'jenisPelanggan', 'tarif', 'areaParkir'])
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar');

        // Prioritaskan pencarian berdasarkan nomor karcis jika tersedia;
        // jika tidak, gunakan nomor polisi yang sudah dinormalisasi.
        if ($nomorKarcis !== '') {
            $query->whereRaw('UPPER(nomor_karcis) = ?', [strtoupper($nomorKarcis)]);
        } else {
            $query->whereRaw('UPPER(plat_nomor) = ?', [strtoupper($identifier)]);
        }

        // Ambil transaksi terbaru jika terdapat lebih dari satu histori aktif
        // dengan identitas yang sama.
        $transaction = $query->latest('waktu_masuk')->first();

        // Transaksi yang tidak ditemukan berarti kendaraan tidak dapat diproses
        // dan harus dikembalikan sebagai error validasi ke form.
        if (! $transaction) {
            throw ValidationException::withMessages([
                'plat_nomor' => 'Data kendaraan tidak ditemukan dalam parkir aktif.',
            ]);
        }

        // Waktu keluar hanya dipakai sebagai waktu simulasi untuk preview.
        // Nilai ini baru ditulis ke transaksi pada storeKeluar().
        $waktuKeluar = now();
        $durasiMenit = (int) max(0, $transaction->waktu_masuk->diffInMinutes($waktuKeluar));

        // Ambil aturan tarif dari pengaturan aplikasi supaya perhitungan
        // preview sama dengan perhitungan saat transaksi benar-benar diproses.
        $gracePeriod = (int) Setting::valueOf('menit_grace_period', 0);
        $halfPriceMinutes = (int) Setting::valueOf('menit_tarif_setengah', 0);
        $jenisPelanggan = $transaction->jenisPelanggan;
        $tarif = $transaction->tarif;

        // Tanpa tarif, sistem tidak dapat menghitung pembayaran secara aman.
        if (! $tarif) {
            throw ValidationException::withMessages([
                'plat_nomor' => 'Tarif untuk kendaraan ini belum tersedia.',
            ]);
        }

        // Denda karcis hilang hanya diterapkan jika checkbox dicentang dan
        // jenis pelanggan tidak memiliki pengecualian denda.
        $dendaKarcisHilang = 0;
        if ($request->boolean('karcis_hilang') && (! $jenisPelanggan || ! $jenisPelanggan->is_bebas_denda)) {
            $dendaKarcisHilang = (int) Setting::valueOf('denda_karcis_hilang', 0);
        }

        // Hitung tarif dasar, lalu tambahkan denda bila memang berlaku.
        $totalBayar = $this->calculateParkingTotal(
            $durasiMenit,
            $gracePeriod,
            $halfPriceMinutes,
            $jenisPelanggan,
            $tarif
        ) + $dendaKarcisHilang;

        // Response ini dikonsumsi JavaScript pada modal preview, sehingga
        // angka dikirim sebagai integer dan tanggal dikirim sebagai string.
        return response()->json([
            'plat_nomor' => $transaction->plat_nomor ?? '-',
            'nomor_karcis' => $transaction->nomor_karcis ?? '-',
            'jenis_kendaraan' => $transaction->jenis_kendaraan ?? $transaction->kendaraan?->jenis_kendaraan ?? 'Tidak diketahui',
            'jenis_pelanggan' => $jenisPelanggan?->nama ?? 'Reguler',
            'area' => $transaction->areaParkir?->nama ?? '-',
            'waktu_masuk' => $transaction->waktu_masuk?->format('d M Y H:i') ?? '-',
            'waktu_keluar' => $waktuKeluar->format('d M Y H:i'),
            'durasi' => $durasiMenit,
            'denda' => $dendaKarcisHilang,
            'total_bayar' => $totalBayar,
        ]);
    }

    /**
     * Menampilkan semua transaksi yang masih aktif di area parkir.
     *
     * Data ini membantu operator mencari kendaraan yang belum keluar dan
     * menjadi sumber informasi pada halaman kendaraan terparkir.
     */
    public function terparkir(): View
    {
        // Muat relasi yang dipakai tabel agar Blade tidak menimbulkan N+1 query.
        $activeTransactions = Transaksi::with(['kendaraan', 'jenisPelanggan', 'areaParkir', 'tarif'])
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        return view('parkir.terparkir', compact('activeTransactions'));
    }

    /**
     * Menerima form kendaraan masuk dari operator.
     *
     * Input yang dibutuhkan dari form:
     * - plat_nomor: nomor polisi yang masuk ke area parkir.
     * - jenis_kendaraan: jenis kendaraan agar sistem bisa memilih tarif yang benar.
     * - area_parkir_id: area yang dipilih untuk parkir.
     *
     * Fungsi ini akan validasi data, memastikan area dan tarif cocok, mengecek apakah
     * kendaraan sudah aktif masuk atau belum, lalu membuat transaksi baru dan mencatat
     * log aktivitas. Hasilnya adalah data transaksi baru yang siap dipakai untuk tiket.
     */
    public function storeMasuk(Request $request): RedirectResponse
    {
        // Validasi field yang berasal dari form sebelum digunakan untuk query
        // atau disimpan ke tabel transaksi.
        $validated = $request->validate([
            'plat_nomor' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-\s]+$/'],
            'jenis_kendaraan' => ['required', 'in:mobil,motor,truk'],
            'area_parkir_id' => ['required', 'uuid', 'exists:area_parkirs,id'],
        ]);

        // Jenis pelanggan reguler menjadi fallback untuk kendaraan yang belum
        // terdaftar, sehingga setiap transaksi tetap memiliki pelanggan.
        $regularType = JenisPelanggan::query()
            ->whereRaw('LOWER(nama) = ?', ['reguler'])
            ->first();

        if (! $regularType) {
            return back()->withInput()->withErrors([
                'plat_nomor' => 'Data jenis pelanggan reguler tidak tersedia mohon tambahkan terlebih dahulu di jenis pelanggan',
            ]);
        }

        // Mengubah plat nomor menjadi huruf besar dan menghapus spasi di awal/akhir.
        $platNomor = strtoupper(trim((string) $validated['plat_nomor']));

        // Cari kendaraan terdaftar tanpa membuat data kendaraan baru.
        // Data pelanggan kendaraan terdaftar dipakai; selain itu gunakan reguler.
        $registeredVehicle = Kendaraan::query()->whereRaw('UPPER(plat_nomor) = ?', [$platNomor])->first();
        $jenisPelanggan = $registeredVehicle?->jenisPelanggan ?? $regularType;

        // Cegah operator memilih jenis kendaraan yang berbeda dari data master.
        if ($registeredVehicle && strtolower((string) $registeredVehicle->jenis_kendaraan) !== strtolower((string) $validated['jenis_kendaraan'])) {
            return back()->withInput()->withErrors([
                'plat_nomor' => 'Nomor polisi ini terdaftar dengan jenis kendaraan '.strtolower((string) $registeredVehicle->jenis_kendaraan).'. Silakan sesuaikan jenis kendaraan.',
            ]);
        }

        // Muat area beserta tarifnya agar pilihan area dan kendaraan dapat
        // diverifikasi sebelum transaksi dibuat.
        $area = AreaParkir::with('tarif')->findOrFail($validated['area_parkir_id']);

        if (! $area->tarif) {
            return back()->withInput()->withErrors([
                'area_parkir_id' => 'Area parkir ini belum memiliki tarif. Silakan atur tarif area terlebih dahulu.',
            ]);
        }

        // Tarif area harus sesuai dengan jenis kendaraan yang masuk.
        if ($area->tarif->jenis_kendaraan !== strtolower((string) $validated['jenis_kendaraan'])) {
            return back()->withInput()->withErrors([
                'area_parkir_id' => 'Area parkir yang dipilih tidak sesuai dengan jenis kendaraan '.strtolower((string) $validated['jenis_kendaraan']).'.',
            ]);
        }

        // Satu kendaraan tidak boleh memiliki dua transaksi aktif sekaligus.
        $existingActiveTransaction = Transaksi::where('plat_nomor', $platNomor)
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->exists();

        if ($existingActiveTransaction) {
            return back()->withInput()->withErrors([
                'plat_nomor' => 'Kendaraan ini sudah masuk dan masih aktif di parkir.',
            ]);
        }

        // Buat nomor karcis unik sebagai identitas transaksi dan barcode.
        $nomorKarcis = $this->generateTicketNumber();

        // Simpan transaksi masuk setelah seluruh aturan bisnis terpenuhi.
        $transaction = Transaksi::create([
            'kendaraan_id' => $registeredVehicle?->id,
            'plat_nomor' => $platNomor,
            'nomor_karcis' => $nomorKarcis,
            'jenis_kendaraan' => $validated['jenis_kendaraan'],
            'jenis_pelanggan_id' => $jenisPelanggan->id,
            'tarif_id' => $area->tarif->id,
            'area_parkir_id' => $area->id,
            'user_id' => Auth::id(),
            'waktu_masuk' => now(),
            'status' => 'masuk',
        ]);

        // Catat aktivitas operator untuk kebutuhan audit dan pembelajaran alur.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Kendaraan masuk: '.$platNomor.' ('.$jenisPelanggan->nama.') | Nomor karcis: '.$transaction->nomor_karcis,
        ]);

        // Kembalikan operator ke form dan kirim ID untuk membuka preview tiket.
        return redirect()->route('parkir.masuk')
            ->with('success', 'Kendaraan berhasil masuk ke area parkir. Nomor karcis: '.$transaction->nomor_karcis)
            ->with('ticket_transaction_id', $transaction->getKey());
    }

    /**
     * Memproses kendaraan keluar dari area parkir.
     *
     * Fungsi ini mengolah input nomor polisi atau nomor karcis, mencari transaksi aktif,
     * menghitung durasi parkir, menetapkan tarif sesuai jenis pelanggan dan kendaraan,
     * menambahkan denda bila karcis hilang, lalu menutup transaksi. Setelah itu sistem
     * menyiapkan link preview tiket keluar untuk ditampilkan di halaman.
     */
    public function storeKeluar(Request $request): RedirectResponse
    {
        // Validasi ulang dilakukan pada tahap simpan karena request preview
        // dan request final merupakan dua request HTTP yang terpisah.
        $validated = $request->validate([
            'plat_nomor' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-\s]+$/'],
            'nomor_karcis' => ['nullable', 'string', 'max:50'],
            'karcis_hilang' => ['nullable', 'boolean'],
        ]);

        // Ambil dua kemungkinan identitas transaksi dari input operator.
        $identifier = trim((string) ($validated['plat_nomor'] ?? ''));
        $nomorKarcis = trim((string) ($validated['nomor_karcis'] ?? ''));

        // Kendaraan harus dicari menggunakan nomor polisi atau nomor karcis.
        if ($identifier === '' && $nomorKarcis === '') {
            return back()->withInput()->withErrors([
                'plat_nomor' => 'Masukkan nomor polisi atau nomor karcis untuk memproses kendaraan keluar.',
            ]);
        }

        // Izinkan nomor karcis diketik pada field nomor polisi agar alur
        // kasir tetap praktis saat membaca karcis fisik.
        if ($nomorKarcis === '' && str_contains(strtoupper($identifier), 'KRC-')) {
            $nomorKarcis = strtoupper($identifier);
            $identifier = '';
        }

        // Hanya transaksi aktif yang boleh ditutup. Histori yang sudah keluar
        // tidak boleh diproses ulang.
        $query = Transaksi::query()
            ->with(['jenisPelanggan', 'tarif'])
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar');

        // Gunakan kolom pencarian yang sesuai dengan input operator.
        if ($nomorKarcis !== '') {
            $query->whereRaw('UPPER(nomor_karcis) = ?', [strtoupper($nomorKarcis)]);
        } else {
            $query->whereRaw('UPPER(plat_nomor) = ?', [strtoupper($identifier)]);
        }

        // Jika beberapa histori cocok, pilih transaksi aktif yang paling baru.
        $transaction = $query->latest('waktu_masuk')->first();

        // Berhentikan proses jika kendaraan tidak sedang terparkir.
        if (! $transaction) {
            return back()->withInput()->withErrors([
                'plat_nomor' => 'Data kendaraan tidak ditemukan dalam parkir aktif.',
            ]);
        }

        // Tentukan waktu keluar dan durasi aktual yang akan disimpan.
        $waktuMasuk = $transaction->waktu_masuk;
        $waktuKeluar = now();
        $durasiMenit = max(0, $waktuMasuk->diffInMinutes($waktuKeluar));
        // Baca aturan tarif dan relasi pelanggan dari transaksi yang ditemukan.
        $gracePeriod = (int) Setting::valueOf('menit_grace_period', 0);
        $halfPriceMinutes = (int) Setting::valueOf('menit_tarif_setengah', 0);
        $jenisPelanggan = $transaction->jenisPelanggan;
        $tarif = $transaction->tarif;

        // Transaksi tanpa tarif tidak boleh ditutup karena total pembayaran
        // tidak dapat dihitung secara benar.
        if (! $tarif) {
            return back()->withInput()->withErrors([
                'plat_nomor' => 'Tarif untuk kendaraan ini belum tersedia.',
            ]);
        }

        // Hitung denda hanya ketika karcis hilang dan pelanggan tidak bebas
        // denda berdasarkan konfigurasi jenis pelanggan.
        $karcisHilang = (bool) $request->boolean('karcis_hilang');
        $dendaKarcisHilang = 0;

        if ($karcisHilang) {
            if (! $jenisPelanggan || ! $jenisPelanggan->is_bebas_denda) {
                $dendaKarcisHilang = (int) Setting::valueOf('denda_karcis_hilang', 0);
            }
        }

        // Hitung total final menggunakan aturan yang sama dengan preview.
        $totalBayar = $this->calculateParkingTotal($durasiMenit, $gracePeriod, $halfPriceMinutes, $jenisPelanggan, $tarif) + $dendaKarcisHilang;

        // Tutup transaksi dengan waktu keluar, durasi, denda, total, dan status
        // final agar kendaraan tidak lagi dianggap sedang parkir.
        $transaction->update([
            'waktu_keluar' => $waktuKeluar,
            'durasi' => $durasiMenit,
            'denda' => $dendaKarcisHilang,
            'total_bayar' => $totalBayar,
            'status' => 'keluar',
        ]);

        // Simpan jejak perubahan agar aktivitas keluar dapat ditelusuri.
        Log::create([
            'user_id' => Auth::id(),
            'action' => 'Kendaraan keluar: '.$transaction->plat_nomor.' | Durasi '.$durasiMenit.' menit | Denda karcis hilang Rp '.number_format($dendaKarcisHilang, 0, ',', '.').' | Total Rp '.number_format($totalBayar, 0, ',', '.'),
        ]);

        // Pesan ini ditampilkan sebagai notifikasi setelah redirect.
        $message = 'Kendaraan keluar berhasil. Total pembayaran: Rp '.number_format($totalBayar, 0, ',', '.');

        if ($karcisHilang) {
            $message .= ' (Karcis hilang: Rp '.number_format($dendaKarcisHilang, 0, ',', '.').')';
        }

        // Kirim ID transaksi agar halaman dapat membuka preview tiket keluar.
        return redirect()->route('parkir.keluar')
            ->with('success', $message)
            ->with('exit_ticket_transaction_id', $transaction->getKey());
    }

    /**
     * Menampilkan preview tiket masuk dalam bentuk PDF di browser.
     *
     * Parameter $transaction adalah data transaksi aktif yang sudah masuk ke sistem.
     * Fungsi ini membangun view tiket dan mengirimkan response PDF tanpa attachment,
     * agar browser menampilkan file di modal/iframe, bukan men-download otomatis.
     */
    public function previewTicket(Transaksi $transaction)
    {
        // Muat relasi yang diperlukan template tiket sebelum membuat PDF.
        $transaction->load(['kendaraan', 'areaParkir', 'jenisPelanggan', 'tarif']);

        // Gunakan stream agar PDF tampil di browser/modal, bukan langsung
        // diunduh sebagai attachment.
        $pdf = Pdf::loadView('tickets.entry', $this->buildTicketEntryData($transaction))
            ->setPaper([0, 0, 220, 420], 'portrait');

        return $pdf->stream('karcis-masuk-'.$transaction->nomor_karcis.'.pdf', ['Attachment' => false]);
    }

    /**
     * Menampilkan preview tiket keluar dalam bentuk PDF.
     *
     * Digunakan setelah kendaraan selesai parkir dan total bayar sudah dihitung.
     * Tujuan utama fungsi ini adalah menampilkan bukti pembayaran dan informasi
     * keluar kendaraan secara rapi dalam format PDF yang bisa dilihat di browser.
     */
    public function previewExitTicket(Transaksi $transaction)
    {
        // Ambil relasi untuk melengkapi informasi tiket keluar.
        $transaction->load(['kendaraan', 'areaParkir', 'jenisPelanggan', 'tarif']);

        // Tampilkan bukti pembayaran sebagai PDF inline di preview tiket.
        $pdf = Pdf::loadView('tickets.exit', $this->buildTicketExitData($transaction))
            ->setPaper([0, 0, 220, 420], 'portrait');

        return $pdf->stream('karcis-keluar-'.$transaction->nomor_karcis.'.pdf', ['Attachment' => false]);
    }

    /**
     * Mengubah model transaksi menjadi data sederhana untuk tiket masuk.
     *
     * Pemisahan ini membuat template PDF hanya bertugas menampilkan data,
     * sedangkan pemilihan field dan fallback ditangani controller.
     *
     * @return array<string, mixed>
     */
    protected function buildTicketEntryData(Transaksi $transaction): array
    {
        // Gunakan tanda "-" untuk data opsional yang belum tersedia agar
        // template tiket tetap dapat dirender tanpa error.
        $nomorKarcis = $transaction->nomor_karcis ?? '-';

        return [
            'nomor_karcis' => $nomorKarcis,
            'plat_nomor' => $transaction->plat_nomor ?? '-',
            'jenis_kendaraan' => $transaction->jenis_kendaraan ?? $transaction->kendaraan?->jenis_kendaraan ?? 'Tidak diketahui',
            'area_nama' => $transaction->areaParkir?->nama ?? '-',
            'waktu_masuk' => $transaction->waktu_masuk?->format('d M Y H:i') ?? '-',
            'barcode' => $this->generateBarcode($nomorKarcis),
        ];
    }

    /**
     * Mengubah transaksi selesai menjadi data yang dibutuhkan tiket keluar.
     *
     * @return array<string, mixed>
     */
    protected function buildTicketExitData(Transaksi $transaction): array
    {
        // Siapkan data khusus tiket keluar, termasuk total pembayaran dan
        // barcode yang bisa dipindai dari karcis.
        $nomorKarcis = $transaction->nomor_karcis ?? '-';

        return [
            'nomor_karcis' => $nomorKarcis,
            'plat_nomor' => $transaction->plat_nomor ?? '-',
            'jenis_kendaraan' => $transaction->jenis_kendaraan ?? $transaction->kendaraan?->jenis_kendaraan ?? 'Tidak diketahui',
            'area_nama' => $transaction->areaParkir?->nama ?? '-',
            'waktu_keluar' => $transaction->waktu_keluar?->format('d M Y H:i') ?? '-',
            'total_bayar' => (int) ($transaction->total_bayar ?? 0),
            'barcode' => $this->generateBarcode($nomorKarcis),
        ];
    }

    /**
     * Membuat barcode PNG berbentuk data URI untuk ditempelkan pada tiket.
     */
    protected function generateBarcode(string $nomorKarcis): string
    {
        // Barcode memakai nomor karcis sebagai data utama identifikasi tiket.
        $generator = new BarcodeGeneratorPNG;

        // Ukuran barcode dibuat tetap agar hasil PDF konsisten di printer tiket.
        $barcode = $generator->getBarcode(
            $nomorKarcis,
            BarcodeGeneratorPNG::TYPE_CODE_128,
            2,
            50
        );

        return 'data:image/png;base64,'.base64_encode($barcode);
    }

    /**
     * Membuat nomor karcis acak yang unik di tabel transaksi.
     */
    protected function generateTicketNumber(): string
    {
        // Ulangi pembuatan nomor sampai tidak ditemukan nomor yang sama
        // pada histori transaksi.
        do {
            $nomorKarcis = 'KRC-'.now()->format('ymd').'-'.strtoupper(Str::random(6));
        } while (Transaksi::where('nomor_karcis', $nomorKarcis)->exists());

        return $nomorKarcis;
    }

    /**
     * Menghitung tarif parkir berdasarkan durasi dan aturan pelanggan.
     *
     * Urutan aturan penting: gratis, grace period, tarif setengah, tarif flat,
     * lalu tarif normal per jam. Hasil akhir selalu dikembalikan sebagai integer.
     */
    protected function calculateParkingTotal(int $durasiMenit, int $gracePeriod, int $halfPriceMinutes, ?JenisPelanggan $jenisPelanggan, $tarif): int
    {
        // Pelanggan gratis tidak perlu membayar tarif maupun biaya waktu.
        if ($jenisPelanggan && $jenisPelanggan->is_gratis_parkir) {
            return 0;
        }

        // Durasi dalam masa toleransi menghasilkan pembayaran nol.
        if ($durasiMenit <= $gracePeriod) {
            return 0;
        }

        // Ambil tarif jam pertama dan jam berikutnya sebagai angka integer
        // agar perhitungan uang tidak memakai nilai pecahan.
        $jamPertama = (int) ($tarif->tarif_jam_pertama ?? 0);
        $jamBerikutnya = (int) ($tarif->tarif_jam_berikutnya ?? 0);

        // Terapkan harga setengah jika durasi masih berada pada rentang promo.
        if ($halfPriceMinutes > 0 && $halfPriceMinutes > $gracePeriod && $durasiMenit <= ($gracePeriod + $halfPriceMinutes)) {
            return (int) round($jamPertama / 2);
        }

        // Pelanggan flat hanya membayar tarif jam pertama berapa pun durasinya.
        if ($jenisPelanggan && $jenisPelanggan->is_parkir_flat) {
            return $jamPertama;
        }

        // Kurangi grace period sebelum menghitung jam yang ditagihkan.
        $durasiSetelahGrace = max(0, $durasiMenit - $gracePeriod);
        $durasiJamPertama = min($durasiSetelahGrace, 60);
        $total = $jamPertama;

        // Setiap jam setelah jam pertama dibulatkan ke atas agar sebagian jam
        // tetap ditagihkan sebagai satu jam penuh.
        if ($durasiSetelahGrace > 60) {
            $total += (int) ceil(($durasiSetelahGrace - 60) / 60) * $jamBerikutnya;
        }

        // Perlindungan tambahan untuk durasi yang tidak menghasilkan waktu
        // tertagih setelah grace period diterapkan.
        if ($durasiSetelahGrace <= 0) {
            return 0;
        }

        return max(0, $total);
    }
}