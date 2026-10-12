<?php

require_once __DIR__ . '/models/Book.php';
require_once __DIR__ . '/models/Member.php';
require_once __DIR__ . '/models/Library.php';


function runTest(string $name, callable $test, bool $expectError = true): void
{
    try {
        $test();
        $result = $expectError ? '  FAIL (no exception thrown)' : '  PASS';
    } catch (Exception $e) {
        $result = $expectError
            ? "  PASS → {$e->getMessage()}"
            : "  FAIL (unexpected exception: {$e->getMessage()})";
    }
    echo "<b>{$name}</b>: {$result}<br>";
}

$library = new Library();

// ========== 1. Normal flow ==========
echo "<h2>1. Normal flow</h2>";

$book1 = new Book("B1", "Clean Code", "Robert C. Martin", "Software", 2008);
$book2 = new Book("B2", "Design Patterns", "Erich Gamma", "Software", 1994);
$book3 = new Book("0", "The Pragmatic Programmer", "Andy Hunt", "Software", 1999);

runTest("Add book 1", fn() => $library->addBook($book1), false);
runTest("Add book 2", fn() => $library->addBook($book2), false);
runTest("Add book with id '0'", fn() => $library->addBook($book3), false);
runTest("Add member M1", fn() => $library->addMember(new Member("M1", "Aisha", "aisha@example.com")), false);
runTest("Borrow B1 by M1", fn() => $library->borrowBook("M1", "B1"), false);

echo "<h3>All books:</h3>";
$library->displayBooks();

echo "<h3>Borrowed books:</h3>";
$library->displayBorrowedBooks();

echo "<h3>Search title 'clean' (case-insensitive):</h3>";
$library->displayBooks($library->searchBookByTitle("clean"));

echo "<h3>Search category 'soft':</h3>";
$library->displayBooks($library->searchBookByCategory("soft"));

runTest("Return B1 by M1", fn() => $library->returnBook("M1", "B1"), false);
runTest("Remove B2", fn() => $library->removeBook("B2"), false);

// ========== 2. Exceptions (all of these must throw) ==========
echo "<h2>2. Exceptions (all must throw)</h2>";

// --- Book validation ---
runTest("Book: empty id", fn() => new Book("", "T", "A", "C", 2000));
runTest("Book: spaces-only title", fn() => new Book("B9", "   ", "A", "C", 2000));
runTest("Book: empty author", fn() => new Book("B9", "T", "", "C", 2000));
runTest("Book: empty category", fn() => new Book("B9", "T", "A", "", 2000));
runTest("Book: year < 1000", fn() => new Book("B9", "T", "A", "C", 999));
runTest("Book: year in the future", fn() => new Book("B9", "T", "A", "C", 2999));

// --- Member validation ---
runTest("Member: empty id", fn() => new Member("", "Ali", "ali@example.com"));
runTest("Member: empty name", fn() => new Member("M9", "   ", "ali@example.com"));
runTest("Member: invalid email 'abc'", fn() => new Member("M9", "Ali", "abc"));
runTest("Member: invalid email 'a@'", fn() => new Member("M9", "Ali", "a@"));

// --- Library: duplicates ---
runTest("Library: duplicate book id", fn() => $library->addBook(new Book("B1", "Dup", "A", "C", 2000)));
runTest("Library: duplicate member id", fn() => $library->addMember(new Member("M1", "Dup", "dup@example.com")));

// --- Library: not found ---
runTest("Remove: book not found", fn() => $library->removeBook("B99"));
runTest("Borrow: member not found", fn() => $library->borrowBook("M99", "B1"));
runTest("Borrow: book not found", fn() => $library->borrowBook("M1", "B99"));
runTest("Return: member not found", fn() => $library->returnBook("M99", "B1"));
runTest("Return: book not found", fn() => $library->returnBook("M1", "B99"));

// --- Borrow / return logic ---
$library->addMember(new Member("M2", "Omar", "omar@example.com"));
$library->borrowBook("M1", "B1");

runTest("Borrow: book already borrowed", fn() => $library->borrowBook("M2", "B1"));
runTest("Remove: book currently borrowed", fn() => $library->removeBook("B1"));
runTest("Return: member did not borrow this book", fn() => $library->returnBook("M2", "B1"));
runTest("Return: book is not borrowed at all", fn() => $library->returnBook("M1", "0"));

echo "<h2>Done</h2>";