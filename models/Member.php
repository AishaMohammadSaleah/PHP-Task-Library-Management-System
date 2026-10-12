<?php
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/../traits/ValidatesInput.php';
class Member
{
    use ValidatesInput;
    private string $id;
    private string $name;
    private string $email;
    /** @var Book[] */
    private array $borrowedBooks;
   
    public function __construct(string $id, string $name, string $email)
    {
        $this->requireNonEmpty($id, "Member ID");
        $this->requireNonEmpty($name, "Member Name");
         $this->requireValidEmail($email);
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->borrowedBooks = [];
    }
   private function requireValidEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address.");
        }
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
 
   public function borrowBook(Book $book): void
    {
        $book->borrow();
        $this->borrowedBooks[] = $book;

    }

    public function returnBook(Book $book): void
    {
        $key = array_search($book, $this->borrowedBooks, true);

        if ($key === false) {
            throw new Exception("This member did not borrow this book.");
        }

        unset($this->borrowedBooks[$key]);
        $this->borrowedBooks = array_values($this->borrowedBooks);
        $book->returnBook();
    }

    public function getBorrowedBooks(): array
    {
        return $this->borrowedBooks;
    }
}
