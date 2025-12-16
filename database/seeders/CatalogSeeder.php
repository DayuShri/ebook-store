<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Authors
        $authors = [
            [
                'id' => Str::uuid(),
                'name' => 'Tere Liye',
                'biography' => 'Penulis Indonesia yang produktif dengan berbagai karya best seller.',
                'photo_url' => 'https://via.placeholder.com/300x400?text=Tere+Liye',
                'website_url' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Andrea Hirata',
                'biography' => 'Penulis novel Laskar Pelangi yang terkenal.',
                'photo_url' => 'https://via.placeholder.com/300x400?text=Andrea+Hirata',
                'website_url' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Dee Lestari',
                'biography' => 'Penulis, penyanyi, dan komposer Indonesia.',
                'photo_url' => 'https://via.placeholder.com/300x400?text=Dee+Lestari',
                'website_url' => 'https://www.deelestari.com',
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Pramoedya Ananta Toer',
                'biography' => 'Sastrawan Indonesia yang karya-karyanya diakui secara internasional.',
                'photo_url' => 'https://via.placeholder.com/300x400?text=Pramoedya',
                'website_url' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Raditya Dika',
                'biography' => 'Penulis, komedian, dan sutradara Indonesia.',
                'photo_url' => 'https://via.placeholder.com/300x400?text=Raditya+Dika',
                'website_url' => 'https://www.radityadika.com',
            ],
        ];

        DB::table('authors')->insert($authors);

        // Create Publishers
        $publishers = [
            [
                'id' => Str::uuid(),
                'name' => 'Gramedia Pustaka Utama',
                'description' => 'Penerbit buku terbesar di Indonesia.',
                'logo_url' => 'https://via.placeholder.com/200x100?text=Gramedia',
                'website_url' => 'https://www.gpu.id',
                'established_year' => 1974,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Bentang Pustaka',
                'description' => 'Penerbit yang fokus pada karya sastra Indonesia.',
                'logo_url' => 'https://via.placeholder.com/200x100?text=Bentang',
                'website_url' => 'https://www.bentangpustaka.com',
                'established_year' => 1986,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Mizan',
                'description' => 'Penerbit buku Islam dan umum.',
                'logo_url' => 'https://via.placeholder.com/200x100?text=Mizan',
                'website_url' => 'https://www.mizan.com',
                'established_year' => 1983,
            ],
        ];

        DB::table('publishers')->insert($publishers);

        // Create Categories
        $categories = [
            [
                'id' => Str::uuid(),
                'name' => 'Fiksi',
                'slug' => 'fiksi',
                'parent_id' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Non-Fiksi',
                'slug' => 'non-fiksi',
                'parent_id' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Komedi',
                'slug' => 'komedi',
                'parent_id' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Sejarah',
                'slug' => 'sejarah',
                'parent_id' => null,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Motivasi',
                'slug' => 'motivasi',
                'parent_id' => null,
            ],
        ];

        DB::table('book_categories')->insert($categories);

        // Create Books
        $books = [
            [
                'id' => Str::uuid(),
                'isbn' => '9786020331775',
                'title' => 'Bumi',
                'subtitle' => 'Seri Bumi - Buku 1',
                'synopsis' => 'Namaku Raib, usiaku 15 tahun, dan aku adalah salah satu dari ribuan remaja yang menjalani kehidupan normal.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Bumi',
                'price' => 75000,
                'discount_percentage' => 10,
                'publication_date' => '2014-01-01',
                'page_count' => 440,
                'language' => 'id',
                'file_format' => 'pdf',
                'file_size_mb' => 2.5,
                'publisher_id' => $publishers[0]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9786020822433',
                'title' => 'Bulan',
                'subtitle' => 'Seri Bumi - Buku 2',
                'synopsis' => 'Petualangan Raib, Seli, dan Ali berlanjut di dunia paralel.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Bulan',
                'price' => 80000,
                'discount_percentage' => 15,
                'publication_date' => '2015-01-01',
                'page_count' => 456,
                'language' => 'id',
                'file_format' => 'pdf',
                'file_size_mb' => 2.8,
                'publisher_id' => $publishers[0]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9789793062792',
                'title' => 'Laskar Pelangi',
                'subtitle' => null,
                'synopsis' => 'Kisah inspiratif tentang perjuangan 10 anak dari keluarga miskin di Belitung.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Laskar+Pelangi',
                'price' => 95000,
                'discount_percentage' => 20,
                'publication_date' => '2005-09-01',
                'page_count' => 529,
                'language' => 'id',
                'file_format' => 'epub',
                'file_size_mb' => 1.8,
                'publisher_id' => $publishers[1]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9786020822440',
                'title' => 'Supernova: Kesatria, Putri, dan Bintang Jatuh',
                'subtitle' => 'Seri Supernova - Buku 1',
                'synopsis' => 'Kisah cinta yang penuh filosofi antara Ferre dan Rana.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Supernova',
                'price' => 85000,
                'discount_percentage' => 0,
                'publication_date' => '2001-12-01',
                'page_count' => 326,
                'language' => 'id',
                'file_format' => 'pdf',
                'file_size_mb' => 2.1,
                'publisher_id' => $publishers[0]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9789799101273',
                'title' => 'Bumi Manusia',
                'subtitle' => 'Tetralogi Buru - Buku 1',
                'synopsis' => 'Novel sejarah tentang kehidupan di Hindia Belanda pada awal abad ke-20.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Bumi+Manusia',
                'price' => 120000,
                'discount_percentage' => 25,
                'publication_date' => '1980-08-01',
                'page_count' => 535,
                'language' => 'id',
                'file_format' => 'pdf',
                'file_size_mb' => 3.2,
                'publisher_id' => $publishers[2]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9786020822457',
                'title' => 'Kambing Jantan',
                'subtitle' => 'Sebuah Catatan Harian Pelajar Bodoh',
                'synopsis' => 'Kumpulan cerita lucu dari kehidupan sehari-hari Raditya Dika.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Kambing+Jantan',
                'price' => 65000,
                'discount_percentage' => 5,
                'publication_date' => '2005-05-01',
                'page_count' => 224,
                'language' => 'id',
                'file_format' => 'epub',
                'file_size_mb' => 1.2,
                'publisher_id' => $publishers[0]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9786020822464',
                'title' => 'Rindu',
                'subtitle' => null,
                'synopsis' => 'Novel tentang cinta, kehilangan, dan harapan.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Rindu',
                'price' => 78000,
                'discount_percentage' => 10,
                'publication_date' => '2014-03-01',
                'page_count' => 368,
                'language' => 'id',
                'file_format' => 'pdf',
                'file_size_mb' => 2.0,
                'publisher_id' => $publishers[0]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9786020822471',
                'title' => 'Hujan',
                'subtitle' => null,
                'synopsis' => 'Kisah tentang persahabatan dan petualangan di masa lalu.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Hujan',
                'price' => 82000,
                'discount_percentage' => 12,
                'publication_date' => '2016-02-01',
                'page_count' => 392,
                'language' => 'id',
                'file_format' => 'pdf',
                'file_size_mb' => 2.3,
                'publisher_id' => $publishers[0]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9786020822488',
                'title' => 'Perahu Kertas',
                'subtitle' => null,
                'synopsis' => 'Kisah cinta Kugy dan Keenan yang penuh rintangan.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Perahu+Kertas',
                'price' => 88000,
                'discount_percentage' => 15,
                'publication_date' => '2009-05-01',
                'page_count' => 444,
                'language' => 'id',
                'file_format' => 'epub',
                'file_size_mb' => 1.9,
                'publisher_id' => $publishers[1]['id'],
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'isbn' => '9786020822495',
                'title' => 'Marmut Merah Jambu',
                'subtitle' => null,
                'synopsis' => 'Kisah lucu tentang kehidupan Raditya Dika di Australia.',
                'cover_image_url' => 'https://via.placeholder.com/400x600?text=Marmut+Merah+Jambu',
                'price' => 68000,
                'discount_percentage' => 8,
                'publication_date' => '2010-06-01',
                'page_count' => 256,
                'language' => 'id',
                'file_format' => 'pdf',
                'file_size_mb' => 1.5,
                'publisher_id' => $publishers[0]['id'],
                'is_active' => true,
            ],
        ];

        DB::table('books')->insert($books);

        // Create Book-Author relationships
        $bookAuthors = [
            ['id' => Str::uuid(), 'book_id' => $books[0]['id'], 'author_id' => $authors[0]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[1]['id'], 'author_id' => $authors[0]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[2]['id'], 'author_id' => $authors[1]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[3]['id'], 'author_id' => $authors[2]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[4]['id'], 'author_id' => $authors[3]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[5]['id'], 'author_id' => $authors[4]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[6]['id'], 'author_id' => $authors[0]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[7]['id'], 'author_id' => $authors[0]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[8]['id'], 'author_id' => $authors[2]['id'], 'author_order' => 1],
            ['id' => Str::uuid(), 'book_id' => $books[9]['id'], 'author_id' => $authors[4]['id'], 'author_order' => 1],
        ];

        DB::table('book_authors')->insert($bookAuthors);

        // Create Book-Category mappings
        $bookCategories = [
            ['id' => Str::uuid(), 'book_id' => $books[0]['id'], 'category_id' => $categories[0]['id']], // Bumi - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[1]['id'], 'category_id' => $categories[0]['id']], // Bulan - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[2]['id'], 'category_id' => $categories[0]['id']], // Laskar Pelangi - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[2]['id'], 'category_id' => $categories[4]['id']], // Laskar Pelangi - Motivasi
            ['id' => Str::uuid(), 'book_id' => $books[3]['id'], 'category_id' => $categories[0]['id']], // Supernova - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[4]['id'], 'category_id' => $categories[0]['id']], // Bumi Manusia - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[4]['id'], 'category_id' => $categories[3]['id']], // Bumi Manusia - Sejarah
            ['id' => Str::uuid(), 'book_id' => $books[5]['id'], 'category_id' => $categories[2]['id']], // Kambing Jantan - Komedi
            ['id' => Str::uuid(), 'book_id' => $books[6]['id'], 'category_id' => $categories[0]['id']], // Rindu - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[7]['id'], 'category_id' => $categories[0]['id']], // Hujan - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[8]['id'], 'category_id' => $categories[0]['id']], // Perahu Kertas - Fiksi
            ['id' => Str::uuid(), 'book_id' => $books[9]['id'], 'category_id' => $categories[2]['id']], // Marmut Merah Jambu - Komedi
        ];

        DB::table('book_category_mappings')->insert($bookCategories);
    }
}
