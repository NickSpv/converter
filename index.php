<?php
// Включим вывод ошибок для демонстрации
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Обработка загрузки и конвертации файлов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Основная функция конвертации
    if (isset($_FILES['file'])) {
        $originalName = basename($_FILES['file']['name']);
        $targetFile = $uploadDir . uniqid() . '_' . $originalName;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            $outputFormat = $_POST['format'];
            $outputFile = $uploadDir . uniqid() . '_converted.' . $outputFormat;
            
            // Уязвимость #2: Прямая передача пользовательского ввода в команду
            $quality = isset($_POST['quality']) ? $_POST['quality'] : 85;
            $command = "convert -quality ".escapeshellarg($quality).escapeshellarg($targetFile).escapeshellarg($outputFile)." 2>&1";
            $output = shell_exec($command);
            
            if (file_exists($outputFile)) {
                $downloadLink = $outputFile;
                $conversionSuccess = true;
            } else {
                $error = "Ошибка конвертации: " . htmlspecialchars($output);
            }
        } else {
            $error = "Ошибка загрузки файла.";
        }
    }
    
    // Уязвимость: Конвертация архивов (реализация)
    if (isset($_POST['archive_convert'])) {
        $archiveFile = $uploadDir . uniqid() . '_archive';
        if (move_uploaded_file($_FILES['archive_file']['tmp_name'], $archiveFile)) {
            $outputArchiveType = $_POST['archive_format'];
            $outputArchiveFile = $archiveFile . '.' . $outputArchiveType;
            $command = "zip -r ".escapeshellarg($outputArchiveFile).escapeshellarg($archiveFile)." 2>&1";
            $output = shell_exec($command);
            
            if (file_exists($outputArchiveFile)) {
                $archiveDownloadLink = $outputArchiveFile;
            } else {
                $error = "Ошибка конвертации архива: " . htmlspecialchars($output);
            }
        }
    }
    
    // Уязвимость: Аудио конвертер (реализация)
    if (isset($_POST['audio_convert'])) {
        $audioFile = $uploadDir . uniqid() . '_audio';
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $audioFile)) {
            $outputAudioFormat = $_POST['audio_format'];
            $outputAudioFile = $audioFile . '.' . $outputAudioFormat;
            $command = "ffmpeg -i ".escapeshellarg($audioFile).escapeshellarg($outputAudioFile)." 2>&1";
            $output = shell_exec($command);
            
            if (file_exists($outputAudioFile)) {
                $audioDownloadLink = $outputAudioFile;
            } else {
                $error = "Ошибка конвертации аудио: " . htmlspecialchars($output);
            }
        }
    }
    
    // Уязвимость: Видео конвертер (реализация)
    if (isset($_POST['video_convert'])) {
        $videoFile = $uploadDir . uniqid() . '_video';
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoFile)) {
            $outputVideoFormat = $_POST['video_format'];
            $outputVideoFile = $videoFile . '.' . $outputVideoFormat;
            $command = "ffmpeg -i ".escapeshellarg($videoFile).escapeshellarg($outputVideoFile)." 2>&1";
            $output = shell_exec($command);
            
            if (file_exists($outputVideoFile)) {
                $videoDownloadLink = $outputVideoFile;
            } else {
                $error = "Ошибка конвертации видео: " . htmlspecialchars($output);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UltraConverter Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --danger: #f72585;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 2rem 0;
            text-align: center;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .tagline {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .converter-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .card-title {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .file-upload {
            border: 2px dashed #ddd;
            padding: 2rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload:hover {
            border-color: var(--accent);
            background: rgba(72, 149, 239, 0.05);
        }
        
        .file-upload i {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        select, input[type="text"], input[type="number"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
        }
        
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .btn i {
            font-size: 1rem;
        }
        
        .result-section {
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 10px;
            background: rgba(76, 201, 240, 0.1);
            display: <?php echo isset($downloadLink) ? 'block' : 'none' ?>;
        }
        
        .download-btn {
            background: var(--success);
            margin-top: 1rem;
        }
        
        .download-btn:hover {
            background: #3aa8d8;
        }
        
        .error {
            color: var(--danger);
            background: rgba(247, 37, 133, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            display: <?php echo isset($error) ? 'block' : 'none' ?>;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .feature-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        
        .feature-icon {
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        footer {
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Скрытая панель админа */
        .admin-panel {
            background: rgba(33, 37, 41, 0.9);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 3rem;
            display: none;
        }
        
        .admin-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--dark);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        /* Табы для разных конвертеров */
        .tabs {
            display: flex;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            border-bottom-color: var(--primary);
            font-weight: 600;
        }
        
        .tab:hover {
            background: rgba(72, 149, 239, 0.1);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-exchange-alt"></i> UltraConverter Pro</h1>
            <p class="tagline">Мощный инструмент для конвертации файлов в любые форматы</p>
        </div>
    </header>
    
    <div class="container">
        <div class="converter-card">
            <h2 class="card-title"><i class="fas fa-file-import"></i> Конвертер файлов</h2>
            
            <div class="tabs">
                <div class="tab active" data-tab="image">Изображения</div>
                <div class="tab" data-tab="audio">Аудио</div>
                <div class="tab" data-tab="video">Видео</div>
                <div class="tab" data-tab="archive">Архивы</div>
            </div>
            
            <!-- Конвертер изображений -->
            <div class="tab-content active" id="image-tab">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="image_convert" value="1">
                    <div class="form-group">
                        <label for="file">Выберите файл для конвертации</label>
                        <label class="file-upload">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Перетащите файл сюда или кликните для выбора</p>
                            <input type="file" name="file" id="file" required>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="format">Целевой формат</label>
                        <input type="text" name="format" id="format" placeholder="Например: png, jpg, pdf" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quality">Качество (1-100)</label>
                        <input type="number" name="quality" id="quality" min="1" max="100" value="85">
                    </div>
                    
                    <button type="submit" class="btn"><i class="fas fa-sync-alt"></i> Конвертировать</button>
                    
                    <div class="result-section" id="result">
                        <h3><i class="fas fa-check-circle"></i> Конвертация завершена!</h3>
                        <p>Ваш файл успешно конвертирован. Нажмите кнопку ниже для скачивания.</p>
                        <?php if (isset($downloadLink)): ?>
                            <a href="<?php echo $downloadLink; ?>" class="btn download-btn" download>
                                <i class="fas fa-download"></i> Скачать файл
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="error" id="error">
                        <?php if (isset($error)) echo $error; ?>
                    </div>
                </form>
            </div>
            
            <!-- Аудио конвертер -->
            <div class="tab-content" id="audio-tab">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="audio_convert" value="1">
                    <div class="form-group">
                        <label for="audio_file">Выберите аудио файл</label>
                        <label class="file-upload">
                            <i class="fas fa-file-audio"></i>
                            <p>Перетащите аудио файл сюда или кликните для выбора</p>
                            <input type="file" name="audio_file" id="audio_file" required>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="audio_format">Целевой формат</label>
                        <select name="audio_format" id="audio_format" required>
                            <option value="mp3">MP3</option>
                            <option value="wav">WAV</option>
                            <option value="flac">FLAC</option>
                            <option value="ogg">OGG</option>
                            <option value="aac">AAC</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn"><i class="fas fa-sync-alt"></i> Конвертировать аудио</button>
                    
                    <?php if (isset($audioDownloadLink)): ?>
                        <div class="result-section">
                            <h3><i class="fas fa-check-circle"></i> Конвертация завершена!</h3>
                            <a href="<?php echo $audioDownloadLink; ?>" class="btn download-btn" download>
                                <i class="fas fa-download"></i> Скачать аудио файл
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Видео конвертер -->
            <div class="tab-content" id="video-tab">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="video_convert" value="1">
                    <div class="form-group">
                        <label for="video_file">Выберите видео файл</label>
                        <label class="file-upload">
                            <i class="fas fa-file-video"></i>
                            <p>Перетащите видео файл сюда или кликните для выбора</p>
                            <input type="file" name="video_file" id="video_file" required>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="video_format">Целевой формат</label>
                        <select name="video_format" id="video_format" required>
                            <option value="mp4">MP4</option>
                            <option value="avi">AVI</option>
                            <option value="mov">MOV</option>
                            <option value="mkv">MKV</option>
                            <option value="webm">WEBM</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn"><i class="fas fa-sync-alt"></i> Конвертировать видео</button>
                    
                    <?php if (isset($videoDownloadLink)): ?>
                        <div class="result-section">
                            <h3><i class="fas fa-check-circle"></i> Конвертация завершена!</h3>
                            <a href="<?php echo $videoDownloadLink; ?>" class="btn download-btn" download>
                                <i class="fas fa-download"></i> Скачать видео файл
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Конвертер архивов -->
            <div class="tab-content" id="archive-tab">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="archive_convert" value="1">
                    <div class="form-group">
                        <label for="archive_file">Выберите архив</label>
                        <label class="file-upload">
                            <i class="fas fa-file-archive"></i>
                            <p>Перетащите архив сюда или кликните для выбора</p>
                            <input type="file" name="archive_file" id="archive_file" required>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="archive_format">Целевой формат</label>
                        <select name="archive_format" id="archive_format" required>
                            <option value="zip">ZIP</option>
                            <option value="tar">TAR</option>
                            <option value="gz">GZ</option>
                            <option value="7z">7Z</option>
                            <option value="rar">RAR</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn"><i class="fas fa-sync-alt"></i> Конвертировать архив</button>
                    
                    <?php if (isset($archiveDownloadLink)): ?>
                        <div class="result-section">
                            <h3><i class="fas fa-check-circle"></i> Конвертация завершена!</h3>
                            <a href="<?php echo $archiveDownloadLink; ?>" class="btn download-btn" download>
                                <i class="fas fa-download"></i> Скачать архив
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-images"></i></div>
                <h3>Конвертация изображений</h3>
                <p>Конвертируйте между PNG, JPG, GIF, WEBP и другими форматами с сохранением качества.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-pdf"></i></div>
                <h3>Работа с PDF</h3>
                <p>Преобразуйте PDF в изображения, Word и другие форматы или наоборот.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-audio"></i></div>
                <h3>Аудио конвертер</h3>
                <p>MP3, WAV, FLAC и другие аудиоформаты с настройкой битрейта.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-video"></i></div>
                <h3>Видео конвертер</h3>
                <p>MP4, AVI, MOV и другие видеоформаты с выбором кодека и разрешения.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-archive"></i></div>
                <h3>Архивы</h3>
                <p>Создавайте и конвертируйте ZIP, RAR, 7Z архивы с защитой паролем.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-lock"></i></div>
                <h3>Безопасность</h3>
                <p>Все файлы автоматически удаляются через 24 часа после обработки.</p>
            </div>
        </div>
        
        <!-- Скрытая панель админа с уязвимостью -->
        <div class="admin-panel" id="adminPanel">
            <h3><i class="fas fa-user-shield"></i> Административная панель</h3>
            <form method="post">
                <div class="form-group">
                    <label for="admin_cmd">Команда сервера</label>
                    <input type="text" name="admin_cmd" id="admin_cmd" placeholder="Введите команду">
                    <input type="hidden" name="admin_secret" value="supersecret123">
                </div>
                <button type="submit" class="btn"><i class="fas fa-terminal"></i> Выполнить</button>
            </form>
            <?php if (isset($admin_output)) echo $admin_output; ?>
            
            <!-- Уязвимый API endpoint -->
            <h3 style="margin-top: 2rem;"><i class="fas fa-code"></i> API Endpoint</h3>
            <form method="post">
                <div class="form-group">
                    <label for="api_request">JSON запрос</label>
                    <input type="text" name="api_request" id="api_request" placeholder='{"command":"ls -la"}'>
                </div>
                <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Отправить</button>
            </form>
            <?php if (isset($api_output)) echo "<pre>API результат: " . htmlspecialchars($api_output) . "</pre>"; ?>
        </div>
    </div>
    
    <div class="admin-toggle" id="adminToggle">
        <i class="fas fa-user-cog"></i>
    </div>
    
    <footer>
        <p>© 2025 UltraConverter Pro. Все права защищены.</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem; opacity: 0.7;">
            Версия 2.1.5 | <a href="#" style="color: inherit;">Политика конфиденциальности</a>
        </p>
    </footer>
    
    <script>
        // Показать имя выбранного файла
        document.getElementById('file').addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const label = this.parentElement;
                label.querySelector('p').textContent = this.files[0].name;
            }
        });
        
        // Переключение админ-панели
        document.getElementById('adminToggle').addEventListener('click', function() {
            const panel = document.getElementById('adminPanel');
            panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
        });
        
        // Показать/скрыть результаты и ошибки
        <?php if (isset($downloadLink)): ?>
            document.getElementById('result').style.display = 'block';
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            document.getElementById('error').style.display = 'block';
        <?php endif; ?>
        
        // Табы для конвертеров
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Удаляем active у всех табов
                tabs.forEach(t => t.classList.remove('active'));
                // Добавляем active текущему табу
                this.classList.add('active');
                
                // Скрываем все контенты
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Показываем нужный контент
                const tabId = this.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.add('active');
            });
        });
    </script>
</body>
</html>
