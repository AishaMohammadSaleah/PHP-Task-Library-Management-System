<?php
require_once __DIR__ . '/models/Book.php';
require_once __DIR__ . '/models/Member.php';
require_once __DIR__ . '/models/Library.php';

$library = new Library();
$message = '';

 
$messages = [
    'book_added'   => 'Book added successfully.',
    'member_added' => 'Member added successfully.',
];
if (isset($_GET['msg'], $messages[$_GET['msg']])) {
    $message = $messages[$_GET['msg']];
}

try {
    $library->load();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'add_book') {
            $book = new Book(
                trim($_POST['id'] ?? ''),
                trim($_POST['book_title'] ?? ''),
                trim($_POST['book_author'] ?? ''),
                trim($_POST['book_category'] ?? ''),
                (int)($_POST['book_publish_year'] ?? 0)
            );
            $library->addBook($book);
            $library->save();
            header('Location: Form.php?msg=book_added');
            exit;
        } elseif ($action === 'add_member') {
            $member = new Member(
                trim($_POST['member_id'] ?? ''),
                trim($_POST['member_name'] ?? ''),
                trim($_POST['member_email'] ?? '')
            );
            $library->addMember($member);
            $library->save();
            header('Location: Form.php?msg=member_added');
            exit;
        } else {
            throw new Exception("Unknown action.");
        }
    }
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
</head>
<body>

<?php if ($message !== ''): ?>
    <p><b><?= htmlspecialchars($message) ?></b></p>
<?php endif; ?>

<h2>Add Book</h2>
<form action="Form.php" method="post">
    <input type="hidden" name="action" value="add_book">

    <label for="id">Book ID</label><br>
    <input type="text" name="id" id="id"><br>

    <label for="book_title">Book Title</label><br>
    <input type="text" name="book_title" id="book_title"><br>

    <label for="book_author">Book Author</label><br>
    <input type="text" name="book_author" id="book_author"><br>

    <label for="book_category">Book Category</label><br>
    <input type="text" name="book_category" id="book_category"><br>

    <label for="book_publish_year">Book Publish Year</label><br>
    <input type="number" name="book_publish_year" id="book_publish_year"><br><br>

    <input type="submit" value="Add Book">
</form>

<h2>Add Member</h2>
<form action="Form.php" method="post">
    <input type="hidden" name="action" value="add_member">

    <label for="member_id">Member ID</label><br>
    <input type="text" name="member_id" id="member_id"><br>

    <label for="member_name">Member Name</label><br>
    <input type="text" name="member_name" id="member_name"><br>

    <label for="member_email">Member Email</label><br>
    <input type="email" name="member_email" id="member_email"><br><br>

    <input type="submit" value="Add Member">
</form>

<h2>Books</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th><th>Title</th><th>Author</th><th>Category</th><th>Year</th><th>Status</th>
    </tr>
    <?php foreach ($library->getBooks() as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b->getId()) ?></td>
            <td><?= htmlspecialchars($b->getTitle()) ?></td>
            <td><?= htmlspecialchars($b->getAuthor()) ?></td>
            <td><?= htmlspecialchars($b->getCategory()) ?></td>
            <td><?= $b->getPublishYear() ?></td>
            <td><?= $b->isAvailable() ? 'available' : 'borrowed' ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Members</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Borrowed books</th>
    </tr>
    <?php foreach ($library->getMembers() as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m->getId()) ?></td>
            <td><?= htmlspecialchars($m->getName()) ?></td>
            <td><?= htmlspecialchars($m->getEmail()) ?></td>
            <td><?= count($m->getBorrowedBooks()) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>