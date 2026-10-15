<?php
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/Member.php';

class Library
{
    /** @var Book[] */
    private array $books = [];
    /** @var Member[] */
    private array $members = [];
    private string $filePath;
    public function __construct(string $filePath = __DIR__ . '/../data.json')
{
    $this->filePath = $filePath;
}
public function save(): void
{
    $data = [
        'books'   => array_map(fn(Book $b) => $b->toArray(), $this->books),
        'members' => array_map(fn(Member $m) => $m->toArray(), $this->members),
    ];

    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false || file_put_contents($this->filePath, $json) === false) {
        throw new Exception("Failed to save library data.");
    }
}
public function load(): void
{
    if (!file_exists($this->filePath)) {
        return; 
    }

    $data = json_decode(file_get_contents($this->filePath), true);
    if (!is_array($data)) {
        throw new Exception("Library data file is corrupted.");
    }

    $this->books = [];
    $this->members = [];

    foreach ($data['books'] ?? [] as $bookData) {
        $this->books[] = Book::fromArray($bookData);
    }

    foreach ($data['members'] ?? [] as $memberData) {
        $member = Member::fromArray($memberData);
        foreach ($memberData['borrowedBookIds'] ?? [] as $bookId) {
            $member->restoreBorrowedBook($this->findBookById($bookId));
        }
        $this->members[] = $member;
    }
}
    private function findOne(array $items, callable $filter): ?object
    {
        foreach ($items as $item) {
            if ($filter($item)) {
                return $item;
            }
        }
        return null;
    }
  private function findBookById(string $id): Book
    {
        $book = $this->findOne($this->books, fn(Book $b) => $b->getId() === $id);

        if ($book === null) {
            throw new Exception("Book with ID {$id} not found.");
        }

        return $book;
    }

    private function findMemberById(string $id): Member
    {
        $member = $this->findOne($this->members, fn(Member $m) => $m->getId() === $id);

        if ($member === null) {
            throw new Exception("Member with ID {$id} not found.");
        }

        return $member;
    }

    public function addBook(Book $book): void
    {
        if ($this->findOne($this->books, fn(Book $b) => $b->getId() === $book->getId()) !== null) {
            throw new Exception("Book with ID {$book->getId()} already exists.");
        }
        $this->books[] = $book;
    }

    public function addMember(Member $member): void
    {
        if ($this->findOne($this->members, fn(Member $m) => $m->getId() === $member->getId()) !== null) {
            throw new Exception("Member with ID {$member->getId()} already exists.");
        }
        $this->members[] = $member;
    }

    public function removeBook(string $id): void
    {
        $book = $this->findBookById($id);

        if (!$book->isAvailable()) {
            throw new Exception("Cannot remove book with ID {$id} because it is currently borrowed.");
        }

        $key = array_search($book, $this->books, true);
        unset($this->books[$key]);
        $this->books = array_values($this->books);
    }


     public function borrowBook(string $memberId, string $bookId): void
    {
        $member = $this->findMemberById($memberId);
        $book = $this->findBookById($bookId);

        $member->borrowBook($book);
    }

    public function returnBook(string $memberId, string $bookId): void
    {
        $member = $this->findMemberById($memberId);
        $book = $this->findBookById($bookId);

        $member->returnBook($book);
    }

    /** @return Book[] */
    public function searchBookByTitle(string $title): array
    {
        return array_values(array_filter(
            $this->books,
            fn(Book $b) => stripos($b->getTitle(), $title) !== false
        ));
    }

    /** @return Book[] */
    public function searchBookByCategory(string $category): array
    {
        return array_values(array_filter(
            $this->books,
            fn(Book $b) => stripos($b->getCategory(), $category) !== false
        ));
    }

    /** @return Book[] */
    public function getBorrowedBooks(): array
    {
        return array_values(array_filter(
            $this->books,
            fn(Book $b) => !$b->isAvailable()
        ));
    }

    public function displayBooks(?array $books = null): void
    {
        $targetBooks = $books ?? $this->books;
        foreach ($targetBooks as $book) {
            $book->getInfo();
            echo '<br>';
        }
    }

    public function displayBorrowedBooks(): void
    {
        $this->displayBooks($this->getBorrowedBooks());
    }
    public function getBooks(): array
{
    return $this->books;
}

/** @return Member[] */
public function getMembers(): array
{
    return $this->members;
}
}