<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\ManagementMember;
use App\Models\Media;
use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * KONTEN CONTOH untuk pratinjau desain (ICMI_DEMO_MODE=true).
 * Bukan data resmi organisasi — produksi harus memakai data yang dikelola via CMS (PRD §12).
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('icmi.demo_mode') || Article::withTrashed()->exists()) {
            return;
        }

        $admin = User::where('email', 'admin@icmicaringin.or.id')->first();
        $writer = User::where('email', 'writer@icmicaringin.or.id')->first();

        $categories = collect([
            'Kegiatan' => 'Dokumentasi agenda dan kegiatan organisasi.',
            'Organisasi' => 'Informasi kelembagaan dan kepengurusan.',
            'Dakwah & Kajian' => 'Kajian Islam dan kegiatan dakwah.',
            'Sosial & Kemasyarakatan' => 'Program sosial dan pemberdayaan masyarakat.',
            'Pendidikan & Pelatihan' => 'Program pendidikan, pelatihan, dan kaderisasi.',
        ])->map(fn ($desc, $name) => Category::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'description' => $desc,
        ]));

        $media = collect();
        foreach (Storage::disk('public')->files('media/demo') as $path) {
            $name = pathinfo($path, PATHINFO_FILENAME);
            [$width, $height] = getimagesize(Storage::disk('public')->path($path));
            $captions = [
                'rapat-pleno' => 'Suasana rapat pleno pengurus ICMI Kecamatan Caringin.',
                'pelantikan' => 'Prosesi pelantikan pengurus masa bakti 2026–2031.',
                'bakti-sosial' => 'Relawan membagikan paket sembako kepada warga.',
                'pelatihan-kader' => 'Peserta pelatihan kader digital mengikuti materi literasi teknologi.',
                'kajian-islam' => 'Kajian rutin bulanan di masjid kecamatan.',
                'silaturahim' => 'Silaturahim keluarga besar ICMI Kecamatan Caringin.',
            ];
            $media[$name] = Media::create([
                'user_id' => $admin?->id,
                'file_name' => basename($path),
                'file_path' => $path,
                'disk' => 'public',
                'mime_type' => 'image/webp',
                'file_size' => Storage::disk('public')->size($path),
                'width' => $width,
                'height' => $height,
                'alt' => $captions[$name] ?? 'Dokumentasi kegiatan ICMI Caringin.',
                'caption' => $captions[$name] ?? null,
            ]);
        }

        $articles = [
            [
                'title' => 'Rapat Pleno Pengurus Tetapkan Arah Program Kerja 2026–2031',
                'cats' => ['Organisasi', 'Kegiatan'], 'cover' => 'rapat-pleno', 'photos' => ['silaturahim', 'pelantikan'],
                'featured' => true, 'days' => 2, 'author' => $admin,
                'excerpt' => 'Rapat pleno perdana masa bakti 2026–2031 menyepakati lima prioritas program kerja yang berfokus pada pendidikan, dakwah, dan pemberdayaan masyarakat.',
                'content' => "Pengurus ICMI Kecamatan Caringin menggelar rapat pleno perdana masa bakti 2026–2031 di aula sekretariat. Rapat dihadiri seluruh ketua bidang dan membahas arah program kerja lima tahun ke depan.\n\nDalam sambutannya, Ketua menekankan pentingnya kolaborasi lintas bidang serta program yang berdampak langsung bagi masyarakat. Setiap bidang diminta menyusun indikator keberhasilan yang terukur.\n\nRapat menyepakati lima prioritas: penguatan kaderisasi, literasi digital anggota, kajian rutin bulanan, bakti sosial berkala, dan pendampingan UMKM lokal. Seluruh program akan dievaluasi setiap triwulan.\n\nRapat pleno berikutnya dijadwalkan akhir bulan ini untuk mematangkan rencana anggaran dan jadwal pelaksanaan tiap program.",
            ],
            [
                'title' => 'Pelantikan Pengurus ICMI Kecamatan Caringin Masa Bakti 2026–2031',
                'cats' => ['Organisasi'], 'cover' => 'pelantikan', 'photos' => ['rapat-pleno'],
                'featured' => false, 'days' => 12, 'author' => $admin,
                'excerpt' => 'Pengurus baru resmi dilantik dan berkomitmen menjadikan organisasi sebagai wadah cendekiawan yang bermanfaat bagi masyarakat Caringin.',
                'content' => "Pelantikan pengurus ICMI Kecamatan Caringin masa bakti 2026–2031 berlangsung khidmat di aula kecamatan. Acara dihadiri perwakilan pengurus wilayah, ulama, dan tokoh masyarakat.\n\nProsesi pelantikan ditandai pembacaan ikrar dan penyematan tanda jabatan oleh perwakilan pengurus wilayah. Dalam sambutannya, Ketua mengajak seluruh pengurus bekerja secara kolektif dan profesional.\n\nSusunan kepengurusan mencakup ketua, wakil ketua, sekretaris, bendahara, serta lima bidang yang memayungi program organisasi selama lima tahun ke depan.\n\nAcara ditutup dengan doa bersama dan sesi foto keluarga besar pengurus.",
            ],
            [
                'title' => 'Bakti Sosial: Berbagi Paket Sembako untuk Warga Prasejahtera',
                'cats' => ['Sosial & Kemasyarakatan'], 'cover' => 'bakti-sosial', 'photos' => ['silaturahim'],
                'featured' => false, 'days' => 6, 'author' => $writer,
                'excerpt' => 'Sebanyak 120 paket sembako disalurkan kepada warga prasejahtera di tiga desa sebagai wujud kepedulian sosial organisasi.',
                'content' => "Bidang Sosial & Kemasyarakatan ICMI Kecamatan Caringin menyalurkan 120 paket sembako kepada warga prasejahtera di tiga desa. Kegiatan ini merupakan program bakti sosial berkala organisasi.\n\nPenyaluran dilakukan bekerja sama dengan perangkat desa agar tepat sasaran. Setiap paket berisi beras, minyak goreng, gula, dan kebutuhan pokok lainnya.\n\nKetua bidang menyampaikan bahwa bakti sosial akan dilanjutkan setiap triwulan dengan sasaran yang diperluas, termasuk santunan untuk lansia dan anak yatim.\n\nWarga penerima manfaat menyampaikan terima kasih atas kepedulian organisasi dan berharap program serupa terus berlanjut.",
            ],
            [
                'title' => 'Pelatihan Kader Digital: Tingkatkan Literasi Teknologi Anggota',
                'cats' => ['Pendidikan & Pelatihan'], 'cover' => 'pelatihan-kader', 'photos' => ['rapat-pleno'],
                'featured' => false, 'days' => 9, 'author' => $writer,
                'excerpt' => 'Pelatihan dua hari membekali anggota muda dengan keterampilan dasar produktivitas digital, keamanan data, dan etika bermedia sosial.',
                'content' => "Bidang Pendidikan & Pelatihan menggelar Pelatihan Kader Digital selama dua hari di ruang pelatihan kecamatan. Pelatihan diikuti 35 anggota muda dari berbagai desa.\n\nMateri mencakup produktivitas digital, keamanan data pribadi, dan etika bermedia sosial. Peserta praktik langsung menyusun konten publikasi kegiatan organisasi.\n\nFasilitator menekankan bahwa cendekiawan Muslim perlu hadir di ruang digital dengan konten yang mencerahkan dan dapat dipertanggungjawabkan.\n\nPeserta terbaik mendapatkan kesempatan bergabung dengan tim publikasi website resmi organisasi.",
            ],
            [
                'title' => 'Kajian Rutin Bulanan Bahas Peran Cendekiawan di Masyarakat',
                'cats' => ['Dakwah & Kajian'], 'cover' => 'kajian-islam', 'photos' => ['silaturahim'],
                'featured' => false, 'days' => 4, 'author' => $writer,
                'excerpt' => 'Kajian bulanan mengangkat tema peran cendekiawan Muslim dalam menjawab persoalan sosial di tingkat kecamatan.',
                'content' => "Kajian rutin bulanan ICMI Kecamatan Caringin kembali digelar di masjid kecamatan dengan tema peran cendekiawan Muslim dalam menjawab persoalan sosial masyarakat.\n\nUstadz pembimbing kajian menguraikan bahwa ilmu yang dimiliki anggota merupakan amanah yang harus diwujudkan dalam manfaat nyata, mulai dari pendidikan hingga ekonomi umat.\n\nSesi diskusi berlangsung hangat dengan pertanyaan seputar pendampingan pelajar, pemberdayaan UMKM, dan kegiatan sosial kemasyarakatan.\n\nKajian rutin akan terus digelar setiap bulan dengan tema yang relevan bagi kebutuhan masyarakat Caringin.",
            ],
            [
                'title' => 'Silaturahim Keluarga Besar ICMI Caringin Pererat Ukhuwah',
                'cats' => ['Kegiatan'], 'cover' => 'silaturahim', 'photos' => ['bakti-sosial'],
                'featured' => false, 'days' => 16, 'author' => $admin,
                'excerpt' => 'Pertemuan keluarga besar anggota dan simpatisan diisi ramah tamah, permainan anak, dan tausiyah singkat menjelang akhir pekan.',
                'content' => "Keluarga besar ICMI Kecamatan Caringin menggelar silaturahim di taman kecamatan. Acara dihadiri anggota, keluarga, dan simpatisan organisasi.\n\nKegiatan diisi ramah tamah, permainan anak, dan tausiyah singkat tentang menjaga ukhuwah di tengah kesibukan masing-masing.\n\nPanitia juga membuka meja informasi bagi warga yang ingin mengenal program organisasi, termasuk kajian rutin dan bakti sosial.\n\nSilaturahim direncanakan menjadi agenda tahunan untuk mempererat hubungan antarangkatan pengurus.",
            ],
            [
                'title' => 'Program Beasiswa Cendekia Dibuka untuk Pelajar Berprestasi',
                'cats' => ['Pendidikan & Pelatihan', 'Sosial & Kemasyarakatan'], 'cover' => 'pelatihan-kader', 'photos' => ['kajian-islam'],
                'featured' => false, 'days' => 20, 'author' => $writer,
                'excerpt' => 'Beasiswa pendidikan tahap pertama menyasar 25 pelajar berprestasi dari keluarga prasejahtera di wilayah Kecamatan Caringin.',
                'content' => "ICMI Kecamatan Caringin membuka pendaftaran Program Beasiswa Cendekia tahap pertama. Program ini menyasar 25 pelajar berprestasi dari keluarga prasejahtera.\n\nBeasiswa mencakup bantuan biaya pendidikan dan pendampingan belajar oleh anggota organisasi yang berprofesi sebagai pendidik dan profesional.\n\nSeleksi dilakukan bersama pihak sekolah berdasarkan prestasi akademik dan kondisi ekonomi keluarga, tanpa pungutan biaya apa pun.\n\nPendaftaran dibuka selama satu bulan melalui sekretariat organisasi pada jam kerja.",
            ],
            [
                'title' => 'Gerakan Ekonomi Umat: Pendampingan UMKM Lokal Naik Kelas',
                'cats' => ['Sosial & Kemasyarakatan', 'Organisasi'], 'cover' => 'bakti-sosial', 'photos' => ['pelatihan-kader'],
                'featured' => false, 'days' => 25, 'author' => $admin,
                'excerpt' => 'Sepuluh pelaku UMKM didampingi dalam pengemasan produk, pencatatan keuangan sederhana, dan pemasaran digital.',
                'content' => "Bidang Ekonomi & Kewirausahaan memulai pendampingan bagi sepuluh pelaku UMKM lokal dalam gerakan ekonomi umat. Pendampingan berlangsung selama tiga bulan.\n\nMateri meliputi perbaikan kemasan produk, pencatatan keuangan sederhana, dan pemasaran digital melalui media sosial serta lokapasar.\n\nAnggota organisasi yang berprofesi di bidang bisnis dan keuangan bertindak sebagai mentor bagi tiap kelompok UMKM.\n\nProgram ditutup dengan pameran produk UMKM pada agenda silaturahim tahunan organisasi.",
            ],
            [
                'title' => 'Agenda Halal Bihalal dan Rapat Kerja Cabang',
                'cats' => ['Kegiatan'], 'cover' => 'silaturahim', 'photos' => [],
                'featured' => false, 'days' => -7, 'author' => $admin, 'status' => ArticleStatus::Scheduled,
                'excerpt' => 'Halal bihalal keluarga besar dan rapat kerja cabang akan digelar pekan depan di aula kecamatan.',
                'content' => "Pengurus akan menggelar halal bihalal keluarga besar ICMI Kecamatan Caringin pekan depan di aula kecamatan.\n\nAcara dirangkai dengan rapat kerja cabang untuk mematangkan jadwal program triwulan berjalan.\n\nSeluruh anggota dan simpatisan diundang hadir dengan membawa serta keluarga masing-masing.",
            ],
            [
                'title' => 'Draf Liputan Pelatihan Jurnalistik Warga',
                'cats' => ['Pendidikan & Pelatihan'], 'cover' => 'pelatihan-kader', 'photos' => [],
                'featured' => false, 'days' => 0, 'author' => $writer, 'status' => ArticleStatus::Draft,
                'excerpt' => 'Catatan awal liputan pelatihan jurnalistik warga untuk buletin organisasi.',
                'content' => "Draf liputan pelatihan jurnalistik warga yang diselenggarakan tim publikasi organisasi.\n\nMateri dasar meliputi penulisan berita, fotografi ponsel, dan verifikasi informasi.\n\nDraf ini masih dalam penulisan dan belum diajukan untuk review.",
            ],
            [
                'title' => 'Usulan Program Taman Baca Masyarakat',
                'cats' => ['Pendidikan & Pelatihan', 'Sosial & Kemasyarakatan'], 'cover' => 'kajian-islam', 'photos' => [],
                'featured' => false, 'days' => 0, 'author' => $writer, 'status' => ArticleStatus::InReview,
                'excerpt' => 'Usulan pembentukan taman baca masyarakat di balai desa dengan koleksi buku sumbangan anggota.',
                'content' => "Bidang Pendidikan mengusulkan pembentukan taman baca masyarakat di balai desa sebagai program literasi organisasi.\n\nKoleksi awal berasal dari sumbangan buku anggota, dilengkapi kegiatan mendongeng akhir pekan untuk anak.\n\nUsulan diajukan kepada Admin untuk diperiksa sebelum diterbitkan.",
            ],
        ];

        foreach ($articles as $data) {
            $status = $data['status'] ?? ArticleStatus::Published;
            $article = Article::create([
                'user_id' => $data['author']?->id ?? $admin?->id,
                'title' => $data['title'],
                'slug' => Article::uniqueSlug($data['title']),
                'excerpt' => $data['excerpt'],
                'content' => $data['content'],
                'status' => $status,
                'published_at' => $status === ArticleStatus::Draft || $status === ArticleStatus::InReview
                    ? null
                    : now()->subDays($data['days'])->setTime(9, 0),
                'is_featured' => $data['featured'],
                'featured_media_id' => $media[$data['cover']]->id ?? null,
            ]);
            $article->categories()->attach($categories->whereIn('name', $data['cats'])->pluck('id'));
            foreach ($data['photos'] as $i => $photo) {
                $article->photos()->attach($media[$photo]->id, ['show_in_gallery' => true, 'sort_order' => $i]);
            }
        }

        OrganizationProfile::create([
            'history' => "ICMI (Ikatan Cendekiawan Muslim se-Indonesia) berdiri pada tahun 1990 sebagai wadah para cendekiawan Muslim Indonesia untuk berkontribusi bagi umat dan bangsa melalui ilmu pengetahuan dan teknologi.\n\nSemangat tersebut hadir di tingkat kecamatan melalui ICMI Kecamatan Caringin, yang menghimpun profesional, pendidik, dan tokoh masyarakat untuk bergerak bersama dalam pendidikan, dakwah, dan pemberdayaan masyarakat.\n\nMasa bakti 2026–2031 menjadi babak penguatan organisasi: pembenahan kelembagaan, digitalisasi publikasi, dan perluasan program yang dirasakan langsung oleh masyarakat Caringin.",
            'vision' => 'Menjadi wadah cendekiawan Muslim yang unggul, kolaboratif, dan bermanfaat bagi masyarakat Kecamatan Caringin.',
            'mission' => "Memperkuat ukhuwah dan kapasitas intelektual anggota melalui kajian dan pelatihan.\nMendorong peran cendekiawan Muslim dalam pendidikan, dakwah, dan pemberdayaan masyarakat.\nMengembangkan kemitraan strategis dengan pemerintah, ulama, dan dunia usaha.\nMenumbuhkan kepedulian sosial melalui program kemanusiaan dan ekonomi umat.",
            'goals' => "Terwujudnya kaderisasi anggota yang berkelanjutan dan terukur.\nMeningkatnya kualitas pendidikan dan literasi masyarakat melalui program organisasi.\nMenguatnya ekonomi umat melalui pendampingan UMKM lokal.\nTerpeliharanya ukhuwah dan kehidupan dakwah yang moderat di Kecamatan Caringin.",
            'chairman_message' => "Assalamu'alaikum warahmatullahi wabarakatuh.\n\nPuji syukur kita panjatkan ke hadirat Allah SWT. Website resmi ini hadir sebagai pusat informasi dan publikasi ICMI Kecamatan Caringin masa bakti 2026–2031.\n\nKami berkomitmen mengelola organisasi secara transparan dan profesional, serta membuka ruang kolaborasi bagi seluruh anggota dan masyarakat. Semoga kanal digital ini menjadi jembatan ilmu, ukhuwah, dan kebermanfaatan.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.",
            'chairman_name' => 'H. Ahmad Syafii, M.Pd.',
        ]);

        $members = [
            ['H. Ahmad Syafii, M.Pd.', 'Ketua', null, 'Memimpin organisasi dan menetapkan arah kebijakan umum.'],
            ['Dr. Hj. Rahmawati Yusuf, M.Si.', 'Wakil Ketua', null, 'Mengoordinasikan bidang-bidang dan mewakili ketua bila berhalangan.'],
            ['Budi Santoso, S.T., M.M.', 'Sekretaris', null, 'Mengelola administrasi, surat-menyurat, dan arsip organisasi.'],
            ['Hj. Dewi Lestari, S.E.', 'Bendahara', null, 'Mengelola keuangan dan pelaporan anggaran organisasi.'],
            ['Rudi Hartono, S.Pd.', 'Ketua Bidang', 'Organisasi & Kaderisasi', 'Memperkuat keanggotaan dan jenjang kaderisasi.'],
            ['Ust. Fahmi Abdullah, Lc.', 'Ketua Bidang', 'Pendidikan & Dakwah', 'Mengelola kajian rutin dan program pendidikan.'],
            ['Ir. Agus Priyanto', 'Ketua Bidang', 'Sosial & Kemasyarakatan', 'Mengkoordinasikan bakti sosial dan program kemanusiaan.'],
            ['H. Dedi Kurniawan, S.E.', 'Ketua Bidang', 'Ekonomi & Kewirausahaan', 'Mendampingi UMKM dan gerakan ekonomi umat.'],
            ['Dr. Fitri Handayani, M.Pd.', 'Ketua Bidang', 'Perempuan & Pemuda', 'Mengembangkan program perempuan dan kepemudaan.'],
        ];
        foreach ($members as $i => [$name, $position, $division, $description]) {
            ManagementMember::create([
                'name' => $name,
                'position' => $position,
                'division' => $division,
                'description' => $description,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}