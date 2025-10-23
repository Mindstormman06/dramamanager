<?php
// Start session at the router level
if (session_status() === PHP_SESSION_NONE) session_start();

$route = $_GET['route'] ?? '';
$route = trim($route, '/');
$routeParts = explode('/', $route);

switch ($routeParts[0]) {
    case '':
    case 'home':
        include 'index.php';
        break;

    case 'album':
        if (file_exists('album/album.php')) {
            $originalDir = getcwd();
            chdir('album');
            include 'album.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    case 'bot':
        if (file_exists('bot/bot_settings.php')) {
            $originalDir = getcwd();
            chdir('bot');
            include 'bot_settings.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    case 'characters':
        $originalDir = getcwd();
        chdir('characters');
        
        if (isset($routeParts[1])) {
            switch ($routeParts[1]) {
                case 'add':
                    if (file_exists('add_character.php')) {
                        include 'add_character.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                case 'edit':
                    if (file_exists('edit_character.php')) {
                        include 'edit_character.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo "404 - Page not found:";
            }
        } else {
            if (file_exists('characters.php')) {
                include 'characters.php';
            } else {
                http_response_code(404);
                echo "404 - Page not found";
            }
        }
        
        chdir($originalDir);
        break;

    case 'costumes':
        $originalDir = getcwd();
        chdir('costumes');
        
        if (isset($routeParts[1])) {
            switch ($routeParts[1]) {
                case 'add':
                    if (file_exists('add_costume.php')) {
                        include 'add_costume.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                case 'edit':
                    if (file_exists('edit_costume.php')) {
                        include 'edit_costume.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo "404 - Page not found";
            }
        } else {
            if (file_exists('costumes.php')) {
                include 'costumes.php';
            } else {
                http_response_code(404);
                echo "404 - Page not found";
            }
        }
        
        chdir($originalDir);
        break;

    case 'ideas':
        if (file_exists('ideas/ideas.php')) {
            $originalDir = getcwd();
            chdir('ideas');
            include 'ideas.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    case 'props':
        $originalDir = getcwd();
        chdir('props');
        
        if (isset($routeParts[1])) {
            switch ($routeParts[1]) {
                case 'add':
                    if (file_exists('add_prop.php')) {
                        include 'add_prop.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                case 'edit':
                    if (file_exists('edit_prop.php')) {
                        include 'edit_prop.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo "404 - Page not found:";
            }
        } else {
            if (file_exists('props.php')) {
                include 'props.php';
            } else {
                http_response_code(404);
                echo "404 - Page not found";
            }
        }
        
        chdir($originalDir);
        break;

    case 'schedule':
        if (file_exists('schedule/schedule.php')) {
            $originalDir = getcwd();
            chdir('schedule');
            include 'schedule.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    case 'scripts':
        $originalDir = getcwd();
        chdir('scripts');
        
        if (isset($routeParts[1])) {
            switch ($routeParts[1]) {
                case 'add':
                    if (file_exists('analyze_script.php')) {
                        include 'analyze_script.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                case 'show':
                    if (file_exists('create_show_from_script.php')) {
                        include 'create_show_from_script.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo "404 - Page not found:";
            }
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        
        chdir($originalDir);
        break;

    case 'shows':
        $originalDir = getcwd();
        chdir('shows');
        
        if (isset($routeParts[1])) {
            switch ($routeParts[1]) {
                case 'add':
                    if (file_exists('add_show.php')) {
                        include 'add_show.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                case 'edit':
                    if (file_exists('edit_show.php')) {
                        include 'edit_show.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo "404 - Page not found:";
            }
        } else {
            if (file_exists('shows.php')) {
                include 'shows.php';
            } else {
                http_response_code(404);
                echo "404 - Page not found";
            }
        }
        
        chdir($originalDir);
        break;

    case 'login':
        if (file_exists('users/login.php')) {
            $originalDir = getcwd();
            chdir('users');
            include 'login.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    case 'link':
        if (file_exists('users/link_teachers.php')) {
            $originalDir = getcwd();
            chdir('users');
            include 'link_teachers.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    case 'info':
        $originalDir = getcwd();
        chdir('users');
        
        if (isset($routeParts[1])) {
            switch ($routeParts[1]) {
                case 'student':
                    if (file_exists('student_info.php')) {
                        include 'student_info.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                case 'linked':
                    if (file_exists('linked_teachers_and_students.php')) {
                        include 'linked_teachers_and_students.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo "404 - Page not found:";
            }
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        
        chdir($originalDir);
        break;

    case 'register':
        $originalDir = getcwd();
        chdir('users');
        
        if (isset($routeParts[1])) {
            switch ($routeParts[1]) {
                case 'user':
                    if (file_exists('signup.php')) {
                        include 'signup.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;

                case 'reset':
                    if (file_exists('reset_password.php')) {
                        include 'reset_password.php';
                    } else {
                        http_response_code(404);
                        echo "404 - Page not found";
                    }
                    break;
                    
                default:
                    http_response_code(404);
                    echo "404 - Page not found:";
            }
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        
        chdir($originalDir);
        break;

    case 'changelog':
        if (file_exists('changelog.php')) {
            $originalDir = getcwd();
            include 'changelog.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    case 'admin':
        if (file_exists('admin/site_settings.php')) {
            $originalDir = getcwd();
            include 'admin/site_settings.php';
            chdir($originalDir);
        } else {
            http_response_code(404);
            echo "404 - Page not found";
        }
        break;

    default:
        http_response_code(404);
        echo "Page not found: " . htmlspecialchars($route);
        break;
}
?>