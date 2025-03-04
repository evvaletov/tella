<?php
function validateTiming($form_start, $captcha_solve, $submission_time) {
    error_log("Timing validation started");
    error_log("Form start: " . $form_start);
    error_log("Captcha solve: " . $captcha_solve);
    error_log("Submission time: " . $submission_time * 1000);

    // All times should be timestamps in milliseconds
    if (!is_numeric($form_start) || !is_numeric($captcha_solve)) {
        error_log('Invalid timing format - form_start: ' . gettype($form_start) . ', captcha_solve: ' . gettype($captcha_solve));
        return false;
    }

    // Convert to integers
    $form_start = (int)$form_start;
    $captcha_solve = (int)$captcha_solve;
    $submission_time = (int)($submission_time * 1000); // Convert PHP time to milliseconds

    // Validate chronological order
    if ($form_start > $captcha_solve || $captcha_solve > $submission_time) {
        error_log(sprintf('Invalid timing sequence - form_start: %d, captcha_solve: %d, submission: %d',
            $form_start, $captcha_solve, $submission_time));
        return false;
    }

    // Calculate time differences
    $time_to_solve = $captcha_solve - $form_start;
    $time_to_submit = $submission_time - $captcha_solve;
    $total_time = $submission_time - $form_start;

    // Define thresholds (in milliseconds)
    $MIN_SOLVE_TIME = 500;     // Minimum time to solve CAPTCHA (0.5 seconds)
    $MIN_SUBMIT_TIME = 500;    // Minimum time after CAPTCHA (0.5 seconds)
    $MAX_TOTAL_TIME = 3600000; // Maximum total time (1 hour)

    // Log timing information
    error_log(sprintf(
        'Form timing details - Total: %.2fs, Solve: %.2fs, Submit: %.2fs',
        $total_time / 1000,
        $time_to_solve / 1000,
        $time_to_submit / 1000
    ));

    // Check against thresholds
    if ($time_to_solve < $MIN_SOLVE_TIME) {
        error_log(sprintf('CAPTCHA solved too quickly: %.2fs (min: %.2fs)',
            $time_to_solve / 1000, $MIN_SOLVE_TIME / 1000));
        return false;
    }

    if ($time_to_submit < $MIN_SUBMIT_TIME) {
        error_log(sprintf('Form submitted too quickly after CAPTCHA: %.2fs (min: %.2fs)',
            $time_to_submit / 1000, $MIN_SUBMIT_TIME / 1000));
        return false;
    }

    if ($total_time > $MAX_TOTAL_TIME) {
        error_log(sprintf('Form took too long: %.2fs (max: %.2fs)',
            $total_time / 1000, $MAX_TOTAL_TIME / 1000));
        return false;
    }

    error_log('Timing validation passed');
    return true;
}
