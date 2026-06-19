<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/includes/admin-functions.php';

requireAdminLogin();

$restrictions = getAllRestrictions();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        if ($name) {
            if (addRestriction($name)) {
                $message = ['type' => 'success', 'text' => 'Обмеження додано'];
                $restrictions = getAllRestrictions();
            }
        }
    }
    
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        if (deleteRestriction($id)) {
            $message = ['type' => 'success', 'text' => 'Обмеження видалено'];
            $restrictions = getAllRestrictions();
        }
    }
}

$pageTitle = 'Медичні обмеження';
ob_start();
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2"><i class="bi bi-heart-pulse"></i> Медичні обмеження</h1>
        <form method="POST" class="d-flex gap-2">
            <input type="hidden" name="action" value="add">
            <input type="text" name="name" class="form-control" placeholder="Назва обмеження" required>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Додати
            </button>
        </form>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $message['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row g-3">
        <?php foreach ($restrictions as $restriction): ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-exclamation-triangle text-warning"></i>
                            <?php echo htmlspecialchars($restriction['name']); ?>
                        </span>
                        <form method="POST" onsubmit="return confirm('Ви впевнені?');">
                            <input type="hidden" name="id" value="<?php echo $restriction['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
require_once 'layout/admin-layout.php';
?>