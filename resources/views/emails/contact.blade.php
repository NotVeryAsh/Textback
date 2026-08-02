<!DOCTYPE html>
<html>
<body style="font-family: system-ui, sans-serif; color: #1f2937;">
    <h2 style="color:#4f46e5;">New contact message</h2>
    <p><strong>From:</strong> {{ $senderName }} &lt;{{ $senderEmail }}&gt;</p>
    <p><strong>Message:</strong></p>
    <p style="white-space: pre-wrap; border-left: 3px solid #e5e7eb; padding-left: 12px;">{{ $body }}</p>
    <hr>
    <p style="font-size:12px;color:#9ca3af;">Sent from the Textback contact form. Reply to this email to respond directly.</p>
</body>
</html>
