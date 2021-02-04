<?php

// Separate handling file to start a new session if there is none (no need to add on every page)

if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
