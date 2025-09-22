<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$user_id = $_SESSION['user_id'];

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);

    if (!empty($msg)) {
        // Save user message
        $stmt = $pdo->prepare("INSERT INTO ai_support (user_id, role, message, created_at) VALUES (?, 'user', ?, NOW())");
        $stmt->execute([$user_id, $msg]);

        // AI logic 
        $reply = generate_fake_ai_reply($msg);

        // Save AI reply
        $stmt = $pdo->prepare("INSERT INTO ai_support (user_id, role, message, created_at) VALUES (?, 'ai', ?, NOW())");
        $stmt->execute([$user_id, $reply]);
    }
    exit;
}
function generate_fake_ai_reply($input) {
    $input = strtolower($input);

    // Define training dataset
    $training = [
        'hello'   => 'Hi there! How can I assist you today?',
        'problem' => 'I’m sorry to hear that. Can you give me more details?',
        'issue'   => 'Sorry for the trouble. Could you describe the issue more clearly?',
        'thanks'  => "You're welcome! Let me know if you need anything else.",
        'bye'     => 'Goodbye! Feel free to reach out again.',
        'payment' => 'If you have payment issues, please logout and login again, try again. Still have question?',
        'account' => 'You can manage your account under Settings > Account.',
        'login'   => 'If you forgot your login details, try resetting your password or contact support.',
        'upgrade' => 'To upgrade your plan, go to Settings > Upgrade.',
        'support' => 'You’re already chatting with Support. How can I help you more?',
        'form'    => 'To design or view saved forms, go to the Form Designer or My Forms section.',
        'qr code' => 'To generate a QR code, navigate to the QR Code Generator from the dashboard.',
    ];

    // Match user input to known responses
    foreach ($training as $keyword => $reply) {
        if (str_contains($input, $keyword)) {
            return $reply;
        }
    }

    // Fallback response
    return "Thanks for your message. A support agent may also follow up if needed.";
}


?>
<!DOCTYPE html>
<html>
<head>
  <title>Support Chat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #eaeaea; }
    .chat-box { max-width: 600px; margin: 40px auto; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px #ccc; }
    .msg-user, .msg-ai { padding: 10px; border-radius: 15px; margin-bottom: 10px; max-width: 70%; position: relative; }
    .msg-user { background: #dcf8c6; margin-left: auto; text-align: right; }
    .msg-ai { background: #f1f0f0; text-align: left; }
    .time { font-size: 0.8em; color: #999; margin-top: 4px; }
    .typing { font-style: italic; color: #888; }
    .faq-btns button { margin: 3px; }
  </style>
</head>
<body>

<div class="chat-box">
  <div id="chat-body" style="height: 400px; overflow-y: scroll;"></div>

  <div class="faq-btns text-center mt-3">
    <button class="btn btn-outline-secondary btn-sm" onclick="sendQuick('Hello')">Hello</button>
    <button class="btn btn-outline-secondary btn-sm" onclick="sendQuick('I have a problem')">I have a problem</button>
    <button class="btn btn-outline-secondary btn-sm" onclick="sendQuick('Thanks')">Thanks</button>
    <button class="btn btn-outline-secondary btn-sm" onclick="sendQuick('How to pay?')">How to pay?</button>
  </div>

  <form id="chat-form" class="input-group mt-3">
    <input type="text" id="msg" class="form-control" placeholder="Type your message..." autocomplete="off">
    <button class="btn btn-primary" type="submit">Send</button>
  </form>
</div>

<script>
function loadChat() {
  fetch('load_messages.php')
    .then(res => res.text())
    .then(data => {
      document.getElementById('chat-body').innerHTML = data;
      document.getElementById('chat-body').scrollTop = document.getElementById('chat-body').scrollHeight;
    });
}
loadChat();
setInterval(loadChat, 3000);

document.getElementById('chat-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const input = document.getElementById('msg');
  const msg = input.value.trim();
  if (!msg) return;
  showTyping();
  fetch('support.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'message=' + encodeURIComponent(msg)
  }).then(() => {
    input.value = '';
    hideTyping();
    loadChat();
  });
});

function sendQuick(msg) {
  document.getElementById('msg').value = msg;
  document.getElementById('chat-form').dispatchEvent(new Event('submit'));
}

function showTyping() {
  const typing = document.createElement('div');
  typing.className = 'msg-ai typing';
  typing.id = 'typing';
  typing.innerText = 'Support is typing...';
  document.getElementById('chat-body').appendChild(typing);
}

function hideTyping() {
  const t = document.getElementById('typing');
  if (t) t.remove();
}
</script>

</body>
</html>
<?php include 'footer.php' ?>