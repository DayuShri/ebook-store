<?php

namespace App\Services\Frontend;

/**
 * Catalog Service - Mock Data Layer
 * 
 * This service provides mock book and category data.
 * Structure matches database schema for easy swap to Http::get() calls.
 */
class CatalogService
{
    /**
     * Get all books with optional filters
     */
    public function getBooks(array $filters = []): array
    {
        $books = $this->getMockBooks();
        
        // Apply category filter
        if (!empty($filters['category'])) {
            $books = array_filter($books, function($book) use ($filters) {
                foreach ($book['categories'] as $cat) {
                    if ($cat['slug'] === $filters['category']) {
                        return true;
                    }
                }
                return false;
            });
        }
        
        // Apply price filter
        if (!empty($filters['price_min'])) {
            $books = array_filter($books, fn($b) => $b['price'] >= $filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $books = array_filter($books, fn($b) => $b['price'] <= $filters['price_max']);
        }
        
        // Apply rating filter
        if (!empty($filters['min_rating'])) {
            $books = array_filter($books, fn($b) => $b['avg_rating'] >= $filters['min_rating']);
        }
        
        // Apply search
        if (!empty($filters['q'])) {
            $query = strtolower($filters['q']);
            $books = array_filter($books, function($book) use ($query) {
                return str_contains(strtolower($book['title']), $query) ||
                       str_contains(strtolower($book['authors'][0]['name'] ?? ''), $query) ||
                       str_contains(strtolower($book['isbn'] ?? ''), $query);
            });
        }
        
        // Sort
        $sort = $filters['sort'] ?? 'newest';
        usort($books, function($a, $b) use ($sort) {
            return match($sort) {
                'price_low' => $a['price'] <=> $b['price'],
                'price_high' => $b['price'] <=> $a['price'],
                'rating' => $b['avg_rating'] <=> $a['avg_rating'],
                default => strtotime($b['publication_date']) <=> strtotime($a['publication_date']),
            };
        });
        
        return array_values($books);
    }

    /**
     * Get featured books for homepage
     */
    public function getFeaturedBooks(int $limit = 8): array
    {
        $books = $this->getMockBooks();
        usort($books, fn($a, $b) => $b['avg_rating'] <=> $a['avg_rating']);
        return array_slice($books, 0, $limit);
    }

    /**
     * Get newest books
     */
    public function getNewestBooks(int $limit = 4): array
    {
        $books = $this->getMockBooks();
        usort($books, fn($a, $b) => strtotime($b['publication_date']) <=> strtotime($a['publication_date']));
        return array_slice($books, 0, $limit);
    }

    /**
     * Get single book by ID
     */
    public function getBook(string $id): ?array
    {
        $books = $this->getMockBooks();
        foreach ($books as $book) {
            if ($book['id'] === $id) {
                return $book;
            }
        }
        return null;
    }

    /**
     * Get all categories with hierarchy
     */
    public function getCategories(): array
    {
        return [
            [
                'id' => 'cat-1',
                'name' => 'Fiksi',
                'slug' => 'fiksi',
                'count' => 15,
                'children' => [
                    ['id' => 'cat-1-1', 'name' => 'Novel', 'slug' => 'novel', 'count' => 8],
                    ['id' => 'cat-1-2', 'name' => 'Cerpen', 'slug' => 'cerpen', 'count' => 4],
                    ['id' => 'cat-1-3', 'name' => 'Fantasi', 'slug' => 'fantasi', 'count' => 3],
                ],
            ],
            [
                'id' => 'cat-2',
                'name' => 'Non-Fiksi',
                'slug' => 'non-fiksi',
                'count' => 12,
                'children' => [
                    ['id' => 'cat-2-1', 'name' => 'Biografi', 'slug' => 'biografi', 'count' => 5],
                    ['id' => 'cat-2-2', 'name' => 'Sejarah', 'slug' => 'sejarah', 'count' => 4],
                ],
            ],
            [
                'id' => 'cat-3',
                'name' => 'Bisnis & Ekonomi',
                'slug' => 'bisnis',
                'count' => 10,
                'children' => [],
            ],
            [
                'id' => 'cat-4',
                'name' => 'Teknologi',
                'slug' => 'teknologi',
                'count' => 8,
                'children' => [
                    ['id' => 'cat-4-1', 'name' => 'Pemrograman', 'slug' => 'pemrograman', 'count' => 5],
                    ['id' => 'cat-4-2', 'name' => 'AI & Machine Learning', 'slug' => 'ai-ml', 'count' => 3],
                ],
            ],
            [
                'id' => 'cat-5',
                'name' => 'Pengembangan Diri',
                'slug' => 'self-improvement',
                'count' => 9,
                'children' => [],
            ],
        ];
    }

    /**
     * Search books
     */
    public function searchBooks(string $query): array
    {
        return $this->getBooks(['q' => $query]);
    }

    /**
     * Mock books data - Indonesian ebooks
     */
    private function getMockBooks(): array
    {
        return [
            [
                'id' => 'book-001',
                'isbn' => '978-602-03-1234-5',
                'title' => 'Laskar Pelangi',
                'subtitle' => 'Novel Inspiratif dari Belitung',
                'synopsis' => 'Kisah inspiratif tentang sepuluh anak dari keluarga miskin yang berjuang menempuh pendidikan di sebuah sekolah Muhammadiyah di Belitung. Novel ini mengisahkan semangat dan mimpi yang tak kenal menyerah.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400&h=600&fit=crop',
                'price' => 89000,
                'discount_percentage' => 15,
                'publication_date' => '2024-06-15',
                'page_count' => 352,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-1', 'name' => 'Bentang Pustaka'],
                'authors' => [['id' => 'auth-1', 'name' => 'Andrea Hirata']],
                'categories' => [
                    ['id' => 'cat-1', 'name' => 'Fiksi', 'slug' => 'fiksi'],
                    ['id' => 'cat-1-1', 'name' => 'Novel', 'slug' => 'novel'],
                ],
                'avg_rating' => 4.8,
                'review_count' => 1250,
            ],
            [
                'id' => 'book-002',
                'isbn' => '978-602-03-2345-6',
                'title' => 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa',
                'subtitle' => 'Cara Mudah untuk Membangun Kebiasaan Baik',
                'synopsis' => 'Buku panduan praktis untuk membangun kebiasaan baik dan menghilangkan kebiasaan buruk. James Clear menunjukkan bagaimana perubahan kecil dapat memberikan hasil yang luar biasa.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400&h=600&fit=crop',
                'price' => 125000,
                'discount_percentage' => 0,
                'publication_date' => '2024-08-01',
                'page_count' => 320,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-2', 'name' => 'Gramedia Pustaka Utama'],
                'authors' => [['id' => 'auth-2', 'name' => 'James Clear']],
                'categories' => [
                    ['id' => 'cat-5', 'name' => 'Pengembangan Diri', 'slug' => 'self-improvement'],
                ],
                'avg_rating' => 4.9,
                'review_count' => 2100,
            ],
            [
                'id' => 'book-003',
                'isbn' => '978-602-03-3456-7',
                'title' => 'Bumi Manusia',
                'subtitle' => null,
                'synopsis' => 'Novel pertama dari Tetralogi Buru karya Pramoedya Ananta Toer. Mengisahkan kehidupan di era kolonial Belanda melalui tokoh Minke, seorang pemuda pribumi yang mendapat pendidikan Eropa.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=400&h=600&fit=crop',
                'price' => 95000,
                'discount_percentage' => 10,
                'publication_date' => '2024-03-20',
                'page_count' => 535,
                'language' => 'id',
                'file_format' => 'EPUB',
                'publisher' => ['id' => 'pub-3', 'name' => 'Lentera Dipantara'],
                'authors' => [['id' => 'auth-3', 'name' => 'Pramoedya Ananta Toer']],
                'categories' => [
                    ['id' => 'cat-1', 'name' => 'Fiksi', 'slug' => 'fiksi'],
                    ['id' => 'cat-1-1', 'name' => 'Novel', 'slug' => 'novel'],
                ],
                'avg_rating' => 4.7,
                'review_count' => 890,
            ],
            [
                'id' => 'book-004',
                'isbn' => '978-602-03-4567-8',
                'title' => 'Clean Code: Panduan Menulis Kode yang Bersih',
                'subtitle' => 'Teknik Praktis untuk Programmer Profesional',
                'synopsis' => 'Panduan komprehensif untuk menulis kode yang bersih, mudah dibaca, dan mudah di-maintain. Wajib dibaca untuk setiap programmer yang ingin meningkatkan kualitas kodenya.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=400&h=600&fit=crop',
                'price' => 185000,
                'discount_percentage' => 20,
                'publication_date' => '2024-09-10',
                'page_count' => 464,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-4', 'name' => 'Informatika'],
                'authors' => [['id' => 'auth-4', 'name' => 'Robert C. Martin']],
                'categories' => [
                    ['id' => 'cat-4', 'name' => 'Teknologi', 'slug' => 'teknologi'],
                    ['id' => 'cat-4-1', 'name' => 'Pemrograman', 'slug' => 'pemrograman'],
                ],
                'avg_rating' => 4.6,
                'review_count' => 567,
            ],
            [
                'id' => 'book-005',
                'isbn' => '978-602-03-5678-9',
                'title' => 'Rich Dad Poor Dad',
                'subtitle' => 'Apa yang Diajarkan Orang Kaya pada Anak-Anak Mereka',
                'synopsis' => 'Buku keuangan personal paling berpengaruh sepanjang masa. Robert Kiyosaki berbagi pelajaran keuangan yang didapatnya dari dua ayah dengan pandangan berbeda tentang uang.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=400&h=600&fit=crop',
                'price' => 98000,
                'discount_percentage' => 0,
                'publication_date' => '2024-05-05',
                'page_count' => 336,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-2', 'name' => 'Gramedia Pustaka Utama'],
                'authors' => [['id' => 'auth-5', 'name' => 'Robert T. Kiyosaki']],
                'categories' => [
                    ['id' => 'cat-3', 'name' => 'Bisnis & Ekonomi', 'slug' => 'bisnis'],
                ],
                'avg_rating' => 4.5,
                'review_count' => 1890,
            ],
            [
                'id' => 'book-006',
                'isbn' => '978-602-03-6789-0',
                'title' => 'Filosofi Teras',
                'subtitle' => 'Filsafat Yunani-Romawi untuk Mental Tangguh',
                'synopsis' => 'Pengenalan filsafat Stoa yang dikemas secara praktis dan relevan untuk kehidupan modern. Buku ini mengajarkan cara menghadapi masalah hidup dengan pikiran tenang.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=400&h=600&fit=crop',
                'price' => 85000,
                'discount_percentage' => 5,
                'publication_date' => '2024-07-22',
                'page_count' => 346,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-5', 'name' => 'Penerbit Buku Kompas'],
                'authors' => [['id' => 'auth-6', 'name' => 'Henry Manampiring']],
                'categories' => [
                    ['id' => 'cat-5', 'name' => 'Pengembangan Diri', 'slug' => 'self-improvement'],
                ],
                'avg_rating' => 4.7,
                'review_count' => 756,
            ],
            [
                'id' => 'book-007',
                'isbn' => '978-602-03-7890-1',
                'title' => 'Sejarah Indonesia Modern',
                'subtitle' => 'Dari Kemerdekaan hingga Reformasi',
                'synopsis' => 'Buku komprehensif tentang perjalanan bangsa Indonesia sejak kemerdekaan 1945 hingga era reformasi. Dilengkapi dengan analisis mendalam tentang berbagai peristiwa penting.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=400&h=600&fit=crop',
                'price' => 145000,
                'discount_percentage' => 0,
                'publication_date' => '2024-08-17',
                'page_count' => 520,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-6', 'name' => 'Kepustakaan Populer Gramedia'],
                'authors' => [['id' => 'auth-7', 'name' => 'M.C. Ricklefs']],
                'categories' => [
                    ['id' => 'cat-2', 'name' => 'Non-Fiksi', 'slug' => 'non-fiksi'],
                    ['id' => 'cat-2-2', 'name' => 'Sejarah', 'slug' => 'sejarah'],
                ],
                'avg_rating' => 4.4,
                'review_count' => 234,
            ],
            [
                'id' => 'book-008',
                'isbn' => '978-602-03-8901-2',
                'title' => 'Machine Learning dengan Python',
                'subtitle' => 'Panduan Praktis untuk Pemula',
                'synopsis' => 'Buku panduan lengkap belajar machine learning menggunakan Python. Cocok untuk pemula yang ingin memulai karir di bidang AI dan data science.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1555949963-aa79dcee981c?w=400&h=600&fit=crop',
                'price' => 175000,
                'discount_percentage' => 25,
                'publication_date' => '2024-10-01',
                'page_count' => 412,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-4', 'name' => 'Informatika'],
                'authors' => [['id' => 'auth-8', 'name' => 'Rudi Hartono']],
                'categories' => [
                    ['id' => 'cat-4', 'name' => 'Teknologi', 'slug' => 'teknologi'],
                    ['id' => 'cat-4-2', 'name' => 'AI & Machine Learning', 'slug' => 'ai-ml'],
                ],
                'avg_rating' => 4.3,
                'review_count' => 189,
            ],
            [
                'id' => 'book-009',
                'isbn' => '978-602-03-9012-3',
                'title' => 'Cantik Itu Luka',
                'subtitle' => null,
                'synopsis' => 'Novel epik karya Eka Kurniawan yang mengisahkan kehidupan Dewi Ayu, seorang perempuan cantik yang dikutuk memiliki anak yang buruk rupa, dan bagaimana kutukan itu mempengaruhi empat generasi.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1476275466078-4007374efbbe?w=400&h=600&fit=crop',
                'price' => 92000,
                'discount_percentage' => 0,
                'publication_date' => '2024-04-10',
                'page_count' => 520,
                'language' => 'id',
                'file_format' => 'EPUB',
                'publisher' => ['id' => 'pub-2', 'name' => 'Gramedia Pustaka Utama'],
                'authors' => [['id' => 'auth-9', 'name' => 'Eka Kurniawan']],
                'categories' => [
                    ['id' => 'cat-1', 'name' => 'Fiksi', 'slug' => 'fiksi'],
                    ['id' => 'cat-1-1', 'name' => 'Novel', 'slug' => 'novel'],
                ],
                'avg_rating' => 4.6,
                'review_count' => 678,
            ],
            [
                'id' => 'book-010',
                'isbn' => '978-602-03-0123-4',
                'title' => 'The Psychology of Money',
                'subtitle' => 'Pelajaran Abadi tentang Kekayaan, Ketamakan, dan Kebahagiaan',
                'synopsis' => 'Morgan Housel mengeksplorasi cara unik manusia dalam berpikir tentang uang dan mengajarkan bagaimana membuat keputusan finansial yang lebih baik.',
                'cover_image_url' => 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?w=400&h=600&fit=crop',
                'price' => 109000,
                'discount_percentage' => 10,
                'publication_date' => '2024-11-05',
                'page_count' => 256,
                'language' => 'id',
                'file_format' => 'PDF',
                'publisher' => ['id' => 'pub-2', 'name' => 'Gramedia Pustaka Utama'],
                'authors' => [['id' => 'auth-10', 'name' => 'Morgan Housel']],
                'categories' => [
                    ['id' => 'cat-3', 'name' => 'Bisnis & Ekonomi', 'slug' => 'bisnis'],
                ],
                'avg_rating' => 4.8,
                'review_count' => 1456,
            ],
        ];
    }
}
