<?php
$twilioClient = null;
$twilio_number = getenv('TWILIO_FROM_NUMBER') ?: '';

// SMS is optional. Configure a Twilio client in deployment if SMS notifications are enabled.
