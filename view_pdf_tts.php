<?php
require 'db.php';

// Get book ID from URL
$book_id = isset($_GET['bk_id']) ? (int)$_GET['bk_id'] : 0;
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

if (!$book_id || !$user_id) {
    die("Invalid request");
}

// Get book metadata
$stmt = $conn->prepare("SELECT title FROM books WHERE bk_id = ? AND user_id = ?");
$stmt->bind_param("ii", $book_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Book not found or you don't have permission to view it");
}

$book_title = $result->fetch_assoc()['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book_title); ?> - PDF Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        #pdf-container {
            position: relative;
            width: 100%;
            height: calc(100vh - 120px);
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        #pdf-viewer {
            width: 100%;
            height: 100%;
        }
        #tts-controls {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        #tts-text {
            display: none;
        }
        .btn-tts {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="book.php">My Library</a>
            <span class="navbar-text"><?php echo htmlspecialchars($book_title); ?></span>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <div id="pdf-container">
            <iframe id="pdf-viewer" src="view_pdf.php?bk_id=<?php echo $book_id; ?>"></iframe>
        </div>
    </div>

    <!-- TTS Controls -->
    <div id="tts-controls">
        <button id="tts-toggle" class="btn btn-primary btn-tts">
            <i class="bi bi-play-fill"></i>
        </button>
    </div>

    <!-- Hidden text container for TTS -->
    <div id="tts-text"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ttsToggle = document.getElementById('tts-toggle');
            const ttsText = document.getElementById('tts-text');
            let speechSynthesis = window.speechSynthesis;
            let utterance = null;
            let isPlaying = false;
            let speechText = '';

            // Fetch text content from PDF
            fetch(`view_pdf.php?bk_id=<?php echo $book_id; ?>&get_text=1`)
                .then(response => response.text())
                .then(text => {
                    speechText = text;
                    ttsText.textContent = text;
                })
                .catch(error => {
                    console.error('Error fetching text:', error);
                    alert('Could not load text for speech synthesis');
                });

            ttsToggle.addEventListener('click', function() {
                if (!speechText) {
                    alert('Text not loaded yet');
                    return;
                }

                if (isPlaying) {
                    // Stop TTS
                    speechSynthesis.cancel();
                    ttsToggle.innerHTML = '<i class="bi bi-play-fill"></i>';
                    isPlaying = false;
                } else {
                    // Start TTS
                    utterance = new SpeechSynthesisUtterance(speechText);
                    
                    // Set voice properties
                    utterance.rate = 1.0;
                    utterance.pitch = 1.0;
                    utterance.volume = 1.0;
                    
                    // Find a good voice
                    const voices = speechSynthesis.getVoices();
                    const preferredVoices = ['Microsoft David', 'Google UK English Male', 'English'];
                    let voice = voices.find(v => preferredVoices.some(p => v.name.includes(p)));
                    if (voice) utterance.voice = voice;
                    
                    utterance.onend = function() {
                        ttsToggle.innerHTML = '<i class="bi bi-play-fill"></i>';
                        isPlaying = false;
                    };
                    
                    speechSynthesis.speak(utterance);
                    ttsToggle.innerHTML = '<i class="bi bi-stop-fill"></i>';
                    isPlaying = true;
                }
            });

            // Load voices when they become available
            speechSynthesis.onvoiceschanged = function() {
                const voices = speechSynthesis.getVoices();
                console.log('Available voices:', voices);
            };
        });
    </script>
</body>
</html>