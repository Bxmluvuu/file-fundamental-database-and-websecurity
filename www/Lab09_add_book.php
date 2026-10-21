<?php
declare(strict_types=1);
require __DIR__ . '/Lab09_config.php';

$form = [
    'title' => '', 'author' => '', 'category' => 'Database',
    'price' => '', 'isbn' => '', 'publish_year' => '', 'stock' => '',
];
$errors = [];
$success = '';
$databaseError = '';
$books = [];
$simulateDatabaseError = ($_GET['database_error_test'] ?? '') === '1';

try {
    $conn = open_database();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($form as $key => $default) {
            $value = $_POST[$key] ?? '';
            // เก็บข้อความไว้แสดงกลับในฟอร์ม; ค่าที่ส่งเป็น array ถือเป็นค่าว่าง
            $form[$key] = is_string($value) ? trim($value) : '';
        }

        // TODO 1: ตรวจข้อมูลฝั่ง PHP แล้วเพิ่มข้อความผิดลง $errors[]
        // เงื่อนไขแต่ละช่องอยู่ใน README.md
        // ตอนนี้มีแค่ข้อจำกัดใน HTML ซึ่งแก้ผ่าน DevTools ได้
        // ตรวจชนิด/รูปแบบ/ช่วงจากข้อความต้นฉบับ ก่อนแปลงเป็น int หรือ float

        if ($errors === []) {
            $title = $form['title'];
            $author = $form['author'];
            $category = $form['category'];
            $isbn = $form['isbn'];
            $price = (float) $form['price'];
            $publishYear = (int) $form['publish_year'];
            $stock = (int) $form['stock'];

            // คอลัมน์นี้ไม่มีจริง ใช้ทดสอบ DB Error โดยไม่ทำลายตาราง
            $titleColumn = $simulateDatabaseError ? 'missing_title_column' : 'title';
            $sql = "INSERT INTO books ($titleColumn, author, category, price, isbn, publish_year, stock)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssdsii', $title, $author, $category, $price, $isbn, $publishYear, $stock);
            $stmt->execute();
            $stmt->close();
            $success = 'บันทึกหนังสือแล้ว';
        }
    }

    $books = $conn->query('SELECT * FROM books ORDER BY id DESC LIMIT 10')->fetch_all(MYSQLI_ASSOC);
    $conn->close();
} catch (mysqli_sql_exception $e) {
    // TODO 2: ตอนนี้เอา Error ภายในมาให้ผู้ใช้เห็น ให้แก้ตามโจทย์ใน README
    // ต้องเก็บรายละเอียดลง Server Log และแสดงข้อความทั่วไปแทน
    $databaseError = $e->getMessage();
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lab 09 — เพิ่มหนังสือ</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f5f7; color: #172733; font: 16px/1.6 system-ui, sans-serif; }
        main { max-width: 920px; margin: 32px auto; padding: 0 20px 40px; }
        h1 { margin-bottom: 4px; font-size: 28px; }
        h2 { font-size: 20px; }
        a { color: #086a86; }
        .card { background: white; border: 1px solid #dbe3e8; border-radius: 12px; padding: 24px; margin: 20px 0; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        label { display: block; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #a6b6c2; border-radius: 6px; font: inherit; margin-top: 4px; }
        button { margin-top: 20px; padding: 10px 24px; border: 0; border-radius: 6px; background: #086a86; color: white; font: inherit; cursor: pointer; }
        .notice { padding: 14px 18px; border-radius: 8px; overflow-wrap: anywhere; }
        .good { background: #e0f4e9; }
        .bad { background: #fde7e6; }
        .hint { color: #506575; }
        .table-wrap { overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; white-space: nowrap; }
        th, td { padding: 10px; border-bottom: 1px solid #dbe3e8; text-align: left; }
        code { background: #edf1f4; padding: 2px 5px; border-radius: 4px; }
        @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <h1>Lab 09 — เพิ่มหนังสือ</h1>
    <p class="hint">แก้ TODO 1 และ TODO 2 ใน <code>www/Lab09_add_book.php</code> ตามโจทย์ใน README</p>
    <p><a href="./">โหมดปกติ</a> · <a href="?database_error_test=1">โหมดทดสอบ DB Error</a></p>

    <?php if ($simulateDatabaseError): ?>
        <p class="notice bad">เปิดโหมด DB Error อยู่: กรอกข้อมูลที่ถูกต้องแล้วกดบันทึก เพื่อทดสอบ catch</p>
    <?php endif; ?>
    <?php if ($success !== ''): ?>
        <p class="notice good" data-testid="success"><?= h($success) ?></p>
    <?php endif; ?>
    <?php if ($errors !== []): ?>
        <ul class="notice bad" data-testid="validation-errors">
            <?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ($databaseError !== ''): ?>
        <p class="notice bad" data-testid="database-error"><?= h($databaseError) ?></p>
    <?php endif; ?>

    <section class="card">
        <h2>เพิ่มหนังสือ</h2>
        <form method="post">
            <div class="grid">
                <label>ชื่อหนังสือ
                    <input name="title" required maxlength="150" value="<?= h($form['title']) ?>">
                </label>
                <label>ผู้เขียน
                    <input name="author" required maxlength="100" value="<?= h($form['author']) ?>">
                </label>
                <label>หมวด
                    <select name="category" required>
                        <?php foreach (['Database', 'Programming', 'Security'] as $categoryOption): ?>
                            <option value="<?= h($categoryOption) ?>" <?= $form['category'] === $categoryOption ? 'selected' : '' ?>><?= h($categoryOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>ราคา (บาท)
                    <input name="price" type="number" required min="0.01" max="999999.99" step="0.01" value="<?= h($form['price']) ?>">
                </label>
                <label>ISBN
                    <input name="isbn" required pattern="([0-9]{9}[0-9Xx]|[0-9]{13})" title="ISBN-10 หรือ ISBN-13 ไม่ใส่ขีด" value="<?= h($form['isbn']) ?>">
                </label>
                <label>ปีพิมพ์ (ค.ศ.)
                    <input name="publish_year" type="number" required min="1900" max="<?= date('Y') ?>" step="1" value="<?= h($form['publish_year']) ?>">
                </label>
                <label>จำนวนคงเหลือ
                    <input name="stock" type="number" required min="0" max="100000" step="1" value="<?= h($form['stock']) ?>">
                </label>
            </div>
            <button type="submit">บันทึกหนังสือ</button>
        </form>
    </section>

    <section class="card">
        <h2>หนังสือ 10 รายการล่าสุด</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>ชื่อหนังสือ</th><th>ผู้เขียน</th><th>หมวด</th><th>ราคา</th><th>ISBN</th><th>ปี</th><th>คงเหลือ</th></tr></thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <?php foreach (['id', 'title', 'author', 'category', 'price', 'isbn', 'publish_year', 'stock'] as $column): ?>
                                <td><?= h((string) $book[$column]) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
