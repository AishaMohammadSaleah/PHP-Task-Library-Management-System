<?php
require_once __DIR__ . '/../traits/ValidatesInput.php';
class Book
{
    private string $id;
    private string $title;
    private string $author;
    private string $category;
    private int $publishYear;
    private bool $isAvailable;
    use ValidatesInput;
    public function __construct(string $id, string $title, string $author, string $category, int $publishYear, bool $isAvailable = true)
    {
        $this->requireNonEmpty($id, "Book ID");
        $this->requireNonEmpty($title, "Book Title");
        $this->requireNonEmpty($author, "Book Author");
        $this->requireNonEmpty($category, "Book Category");
        $this->requireValidYear($publishYear);
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->category = $category;
        $this->publishYear = $publishYear;
        $this->isAvailable = $isAvailable;
    }
 
    private function requireValidYear(int $year): void
    {
        if ($year < 1000 || $year > (int)date("Y")) {
            throw new InvalidArgumentException("Publish year is invalid.");
        }
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getCategory(): string
    {
        return $this->category;
    }
    public function getAuthor(): string
    {
        return $this->author;
    }
    public function getPublishYear(): int
    {
        return $this->publishYear;
    }

    public function borrow(): void
    {
        if (!$this->isAvailable) {
            throw new Exception("Book is already borrowed.");
        }
        $this->isAvailable = false;
    }

    public function returnBook(): void
    {
        if ($this->isAvailable) {
            throw new Exception("Book is already available.");
        }
        $this->isAvailable = true;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function getInfo(): void
    {
        $status = $this->isAvailable ? 'available' : 'borrowed';
        $year = $this->publishYear;

        echo 'Book Title: ' . $this->title . '<br>' .
            'Author: ' . $this->author . '<br>' .
            'Category: ' . $this->category . '<br>' .
            'Publish Year: ' . $year . '<br>' .
            'Status: ' . $status . '<br>';
    }
}
