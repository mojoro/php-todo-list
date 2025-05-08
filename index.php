<?php
session_start();

$taskFile = 'tasks.json';

// Load tasks from file
function loadTasks($taskFile) {
    if (!file_exists($taskFile)) {
        file_put_contents($taskFile, json_encode([]));
    }
    $data = file_get_contents($taskFile);
    return json_decode($data, true);
}

// Save tasks to file
function saveTasks($taskFile, $tasks) {
    file_put_contents($taskFile, json_encode($tasks, JSON_PRETTY_PRINT));
}

// Add task
function addTask(&$tasks, $title) {
    $tasks[] = [
        'id' => uniqid(),
        'title' => htmlspecialchars($title),
        'completed' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Delete task
function deleteTask(&$tasks, $id) {
    $tasks = array_filter($tasks, fn($task) => $task['id'] !== $id);
}

// Toggle completion
function toggleTask(&$tasks, $id) {
    foreach ($tasks as &$task) {
        if ($task['id'] === $id) {
            $task['completed'] = !$task['completed'];
            break;
        }
    }
}

// Main logic
$tasks = loadTasks($taskFile);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add' && !empty($_POST['title'])) {
            addTask($tasks, $_POST['title']);
        }

        if ($action === 'delete' && isset($_POST['id'])) {
            deleteTask($tasks, $_POST['id']);
        }

        if ($action === 'toggle' && isset($_POST['id'])) {
            toggleTask($tasks, $_POST['id']);
        }

        saveTasks($taskFile, $tasks);
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Task Manager</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 20px; }
        h1 { color: #333; }
        .task { padding: 10px; background: #fff; border: 1px solid #ddd; margin: 10px 0; display: flex; justify-content: space-between; align-items: center; }
        .completed { text-decoration: line-through; color: gray; }
        form.inline { display: inline; }
        input[type="text"] { padding: 10px; width: 300px; }
        input[type="submit"] { padding: 10px 15px; }
    </style>
</head>
<body>

<h1>Task Manager</h1>

<form method="POST">
    <input type="text" name="title" placeholder="New Task..." required>
    <input type="hidden" name="action" value="add">
    <input type="submit" value="Add Task">
</form>

<hr>

<?php if (empty($tasks)): ?>
    <p>Looking a little lazy there. Add some tasks!</p>
<?php else: ?>
    <?php foreach ($tasks as $task): ?>
        <div class="task">
            <span class="<?= $task['completed'] ? 'completed' : '' ?>">
                <?= $task['title'] ?> <small>(created <?= $task['created_at'] ?>)</small>
            </span>
            <div>
                <form method="POST" class="inline">
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                    <input type="hidden" name="action" value="toggle">
                    <input type="submit" value="<?= $task['completed'] ? 'Undo' : 'Complete' ?>">
                </form>

                <form method="POST" class="inline">
                    <input type="hidden" name="id" value="<?= $task['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="submit" value="Delete" onclick="return confirm('Are you sure?')">
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
