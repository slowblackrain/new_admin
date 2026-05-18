<?php
$html = file_get_contents('C:/Users/shw/.gemini/antigravity/brain/f9655f6d-5c3e-4d76-a269-b43d68a362ae/walkthrough.md');
$html = str_replace('/C:/Users', 'file:///C:/Users', $html);
file_put_contents('C:/Users/shw/.gemini/antigravity/brain/f9655f6d-5c3e-4d76-a269-b43d68a362ae/walkthrough.md', $html);
