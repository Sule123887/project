<?php
include 'db.php';

// Fetch all quizzes ordered by title then date/time
$res = $conn->query("SELECT * FROM quizzes ORDER BY title ASC, date DESC, time DESC");

$quizzes = [];
while ($row = $res->fetch_assoc()) {
    $quizzes[$row['title']][] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Quizzes</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .quiz-card {
            transition: transform 0.2s;
        }
        .quiz-card:hover {
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(to right, #007bff, #00c6ff);
        }
        .search-box {
            max-width: 400px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-4">
        <h2><i class="bi bi-journal-text"></i> Online Quizzes</h2>
        <p class="text-muted">Browse all available quizzes and start</p>
    </div>

    <!-- Search bar -->
    <div class="d-flex justify-content-center mb-5">
        <input type="text" id="searchInput" onkeyup="filterTitles()" class="form-control search-box" placeholder="Search quiz title...">
    </div>

    <!-- Quiz cards -->
    <div id="quizContainer">
    <?php foreach ($quizzes as $title => $quizList): ?>
        <div class="card mb-4 shadow-sm quiz-card quiz-group">
            <div class="card-header text-white">
                <h5 class="mb-0"><i class="bi bi-book"></i> <?= htmlspecialchars($title) ?></h5>
            </div>
            <ul class="list-group list-group-flush">
                <?php
                $count = 1;
                foreach ($quizList as $quiz):
                    $date = htmlspecialchars($quiz['date']);
                    $time = date("h:i A", strtotime($quiz['time']));
                    $link = htmlspecialchars($quiz['link']);
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold"><i class="bi bi-pencil-square"></i> Take Quiz <?= $count ?></span><br>
                        <small class="text-muted"><i class="bi bi-calendar-event"></i> <?= $date ?> &nbsp;&nbsp; <i class="bi bi-clock"></i> <?= $time ?></small>
                    </div>
                    <a href="<?= $link ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="bi bi-box-arrow-up-right"></i> Start
                    </a>
                </li>
                <?php $count++; endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- JavaScript for Search -->
<script>
function filterTitles() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let cards = document.querySelectorAll('.quiz-group');

    cards.forEach(card => {
        let title = card.querySelector('.card-header h5').textContent.toLowerCase();
        if (title.includes(input)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

</body>
</html>
