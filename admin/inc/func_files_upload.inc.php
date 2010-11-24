<?php
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2006,  Sergey Efremov                                        #
#                                                                              #
#   admin/inc/func_files_upload.inc.php                                        #
#   Процедуры по загрузке файлов и катринок.                                   #
#                                                                              #
################################################################################
/*

  function upload_simpl_file() Загрузка оджно файла без доп поверок по указанному пути.
  function upLoadFile()       Загрузка картинок с изменением размеров. 
  function fileRename()       Переименовка файла если в указанной папке уже существует такой фаил.
  
*/

function upload_simple_file ($upload_to_path, $filename_new = '') {
    $errors= '';
    if(isset($_FILES)) {
        foreach ($_FILES as $key => $value) {
            if (is_uploaded_file($_FILES[$key]["tmp_name"])) {
                $filename = $_FILES[$key]["tmp_name"];
                if ($filename_new == '') $filename_new = $filename;
                if (!copy($filename, $upload_to_path.$filename_new)) {
                    $errors .= "Файлы не записались<br>";
                }
            } else {
                $errors .= "Файлы не закачались<br>";
            }
        }
    } else {
        $errors .= "Массива _FILES не существует<br>";
    }
}


function upLoadFile( $dir, $limits = array(), 
    $max_width = 2000, $max_height = 2000 )
{   
    if( !isset( $_FILES[ 'files' ] ) ) return false;
    
    $errors = '';
    $files = array();
    
    foreach( $_FILES[ 'files' ][ 'name' ] as $key => $value )
    {
        $tmp_filename = $_FILES[ 'files' ][ 'tmp_name' ][ $key ];
        $original_filename = $_FILES[ 'files' ][ 'name' ][ $key ];

        if( !is_uploaded_file( $tmp_filename ) )
        {
            $errors .= 'Файл "' . $original_filename 
                . '" не закачивался на сервер!<br>';
            continue;
        }
        
        $size = getimagesize( $tmp_filename );
        if( !$size )
        {
            $errors .= 'Ошибка при определении размеров изображения в файле "' 
                . $original_filename . '"!<br>';
            continue;
        }

        // Поддерживаются форматы JPG, GIF, BMP.
        if( !in_array( $size[ 2 ], array( 1, 2, 6 ) ) )
        {
            $errors .= 'Формат файла "' . $original_filename 
                . '" неизвестен или не поддерживается!<br>';
            continue;
        }
                
        if( ( $size[ 0 ] > $max_width ) || ( $size[ 1 ] > $max_height ) ) 
        {
            $errors .= 'Файл "' . $original_filename 
                . '" превышает максимальный размер ' . $max_width . ' x ' 
                . $max_height . ' точек!<br>';
            continue;
        }

        // Сформировать новое имя файла.
        $new_filename = file_rename( $original_filename, $dir );
        
        // Переместить файл в указанный каталог.
        if( !move_uploaded_file( $tmp_filename, $dir . $new_filename ) )
        {
            $errors .= 'Ошибка при копировании файла "' . $original_filename 
                . '"!<br>';
            continue;
        }

        // Получить ограничения на размеры.
        $limit_width = isset( $limits[ $key ][ 0 ] ) ? $limits[ $key ][ 0 ] : 0;
        $limit_height = isset( $limits[ $key ][ 1 ] ) ? $limits[ $key ][ 1 ] : 0;
        
        // Вычислить коэффициент преобразования в зависимости от указанных
        // требований к размерам.
        $ratio = ( $limit_width && $limit_height ) 
            ? min( $size[ 0 ] / $limit_width, $size[ 1 ] / $limit_height )
            : ( $limit_width  ? $size[ 0 ] / $limit_width
            : ( $limit_height ? $size[ 1 ] / $limit_height 
            : 1 ) );

        // При необходимости изменить размеры.
        if( $ratio != 1 ) 
        {
            resize( $dir . $new_filename, $size[ 0 ] / $ratio, $size[ 1 ] / $ratio );
        }
        
        $files[ $key ] = $new_filename;
    }
    
    if( $files ) return $files;
    
    return false;
}

// Проверяет, существует ли в каталоге $dir файл с указанным именем,
// и при необходимости изменяет имя файла до уникального.
function file_rename( $filename, $dir )
{
    if( file_exists( $dir . $filename ) )
    {
        $char = array( 
            'f', 'a', 't', 'h', 'o', 'm', '_', 'w', 
            'e', 'r', 's', 'd', 'q', 'g', 'z', 'x', 
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '0' 
        );
        srand( ( double )microtime() * 1000000 );
        $unic = $char[ rand( 0, 15 ) ];
        $filename = $unic . $filename;
        return file_rename( $filename, $dir );
    }
    else
    {
        return $filename;
    }
}

// Изменяет размеры указанного файла.
function resize( $file, $w, $h )
{
    $data = getimagesize( $file );

    $resampled = imagecreatetruecolor( $w,$h );
    
    switch( $data[ 2 ] )
    {
        case 1:
        {
            // GIF
            $original = imagecreatefromgif( $file );
            break;
        }
        
        case 2:
        {
            // JPG
            $original = imagecreatefromjpeg( $file );
            break;
        }
        
        case 3:
        {
            // BMP
            $original = imagecreatefromwbmp( $file );
            break;
        }
        
        default:
        {
            // Вообще непонятно, что мы делаем в этой функции.
            $original = '';
        }
    }

    // Если чтение изображения завершилось с ошибкой, вернуться.    
    if( !$original ) return;

    // Преобразовать размеры изображения.    
    imagecopyresampled( $resampled, $original, 0, 0, 0, 0, $w, $h, $data[ 0 ], $data[ 1 ] );

    // Вывести результат преобразования в исходный файл.    
    switch( $data[ 2 ] )
    {
        case 1:
        {
            // GIF
            imagegif( $resampled, $file );
            break;
        }
        
        case 2:
        {
            // JPG
            imagejpeg( $resampled, $file, 80 );
            break;
        }
        
        case 3:
        {
            // BMP
            imagewbmp( $resampled, $file );
            break;
        }
    }
}   


?>
