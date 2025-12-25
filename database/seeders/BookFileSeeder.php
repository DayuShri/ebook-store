<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Library\Models\BookFile;
use Illuminate\Support\Str;

class BookFileSeeder extends Seeder
{
    public function run(): void
    {
        $bookFiles = [
            [
                'book_id' => 'book-001',
                'file_format' => 'pdf',
                'file_path' => 'books/book-001.pdf',
                'file_size_mb' => 1.0,
            ],
            [
                'book_id' => 'book-002',
                'file_format' => 'pdf',
                'file_path' => 'books/book-002.pdf',
                'file_size_mb' => 2.0,
            ],
            [
                'book_id' => 'book-003',
                'file_format' => 'pdf',
                'file_path' => 'books/book-003.pdf',
                'file_size_mb' => 1.5,
            ],
        ];

        foreach ($bookFiles as $fileData) {
            BookFile::firstOrCreate(
                [
                    'book_id' => $fileData['book_id'],
                    'file_format' => $fileData['file_format'],
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'file_path' => $fileData['file_path'],
                    'file_size_mb' => $fileData['file_size_mb'],
                ]
            );
        }

        $this->command->info('Book files seeded successfully!');
    }
}
