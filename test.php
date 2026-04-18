<?php
header('Content-Type: text/html; charset=utf-8');

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post_value($key)
{
    return trim((string) ($_POST[$key] ?? ''));
}

function upload_error_message($code)
{
    switch ((int) $code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return '上传文件过大。';
        case UPLOAD_ERR_PARTIAL:
            return '照片上传不完整。';
        case UPLOAD_ERR_NO_TMP_DIR:
            return '服务器缺少临时目录。';
        case UPLOAD_ERR_CANT_WRITE:
            return '服务器写入照片失败。';
        case UPLOAD_ERR_EXTENSION:
            return '照片上传被 PHP 扩展中断。';
        default:
            return '照片上传失败。';
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title>表单提交入口</title>
    </head>
    <body>
        <h2>请先通过表单提交数据</h2>
        <p><a href="test.html">返回 test.html</a></p>
    </body>
    </html>
    <?php
    exit;
}

$fieldLabels = [
    'username' => '姓名',
    'major' => '专业',
    'age' => '年龄',
    'education' => '学历',
    'nationality' => '民族',
    'phone' => '联系方式',
    'politics' => '政治面貌',
    'email' => '邮箱',
    'college_period' => '大学时间',
    'college_name' => '大学学校',
    'college_major' => '大学专业',
    'highschool_period' => '高中时间',
    'highschool_name' => '高中学校',
    'score' => '高考分数',
    'evaluation' => '自我评价',
];

$submittedData = [];
foreach ($fieldLabels as $key => $label) {
    $submittedData[$key] = post_value($key);
}

$submittedData['hobbies'] = [];
foreach (['hobby1', 'hobby2', 'hobby3', 'hobby4', 'hobby5'] as $hobbyKey) {
    $hobbyValue = post_value($hobbyKey);
    if ($hobbyValue !== '') {
        $submittedData['hobbies'][] = $hobbyValue;
    }
}

$submittedData['submitted_at'] = date('Y-m-d H:i:s');

$photoResult = [
    'original_name' => '',
    'saved_path' => '',
    'status' => '未上传照片',
];

$messages = [];

if (isset($_FILES['photo']) && is_array($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $photo = $_FILES['photo'];
    $photoError = (int) ($photo['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($photoError === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $originalName = basename((string) $photo['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $photoResult['status'] = '仅支持 jpg、jpeg、png、gif、webp 格式的图片。';
        } else {
            $photoDir = __DIR__ . DIRECTORY_SEPARATOR . 'photo';
            if (!is_dir($photoDir) && !mkdir($photoDir, 0777, true) && !is_dir($photoDir)) {
                $photoResult['status'] = '无法创建 photo 文件夹。';
            } else {
                $newPhotoName = date('YmdHis') . '_' . str_replace('.', '', uniqid('', true)) . '.' . $extension;
                $targetPath = $photoDir . DIRECTORY_SEPARATOR . $newPhotoName;

                if (move_uploaded_file((string) $photo['tmp_name'], $targetPath)) {
                    $photoResult = [
                        'original_name' => $originalName,
                        'saved_path' => 'photo/' . $newPhotoName,
                        'status' => '照片上传并转存成功',
                    ];
                } else {
                    $photoResult['status'] = '照片转存失败，请检查 PHP 运行环境。';
                }
            }
        }
    } else {
        $photoResult['status'] = upload_error_message($photoError);
    }
}

$record = [
    'submitted_at' => $submittedData['submitted_at'],
    'form_data' => $submittedData,
    'photo' => $photoResult,
];

$jsonPath = __DIR__ . DIRECTORY_SEPARATOR . 'test.json';
$history = [];

if (is_file($jsonPath)) {
    $existingJson = trim((string) file_get_contents($jsonPath));
    if ($existingJson !== '') {
        $decoded = json_decode($existingJson, true);
        if (is_array($decoded)) {
            $history = $decoded;
        }
    }
}

$history[] = $record;
$jsonContent = json_encode($history, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

if ($jsonContent === false || file_put_contents($jsonPath, $jsonContent, LOCK_EX) === false) {
    $messages[] = 'test.json 写入失败。';
} else {
    $messages[] = '表单数据已经写入 test.json。';
}

$messages[] = $photoResult['status'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>表单提交结果</title>
    <style>
        body {
            font-family: "Microsoft YaHei", sans-serif;
            margin: 30px;
            line-height: 1.7;
            color: #222;
        }

        .card {
            max-width: 920px;
            margin: 0 auto;
            padding: 24px;
            border-radius: 12px;
            background: #f8fbff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            background: #fff;
        }

        th,
        td {
            border: 1px solid #d8e3ef;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            width: 180px;
            background: #eef5fb;
        }

        .message {
            padding: 12px 14px;
            margin: 10px 0;
            border-left: 4px solid #2d7ef7;
            background: #edf4ff;
        }

        .photo-preview {
            max-width: 180px;
            max-height: 220px;
            border: 1px solid #d8e3ef;
            padding: 4px;
            background: #fff;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>表单提交成功</h2>
        <?php foreach ($messages as $message): ?>
            <div class="message"><?php echo e($message); ?></div>
        <?php endforeach; ?>

        <table>
            <tbody>
                <?php foreach ($fieldLabels as $key => $label): ?>
                    <tr>
                        <th><?php echo e($label); ?></th>
                        <td><?php echo nl2br(e($submittedData[$key])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th>爱好</th>
                    <td><?php echo e(empty($submittedData['hobbies']) ? '未填写' : implode('、', $submittedData['hobbies'])); ?></td>
                </tr>
                <tr>
                    <th>照片保存结果</th>
                    <td>
                        <div><?php echo e($photoResult['status']); ?></div>
                        <?php if ($photoResult['saved_path'] !== ''): ?>
                            <div>保存路径：<?php echo e($photoResult['saved_path']); ?></div>
                            <div>原始文件名：<?php echo e($photoResult['original_name']); ?></div>
                            <p><img class="photo-preview" src="<?php echo e($photoResult['saved_path']); ?>" alt="上传照片预览"></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <p><a href="test.html">返回表单页面</a></p>
    </div>
</body>
</html>
