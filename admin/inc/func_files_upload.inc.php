<?php
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2006,  Sergey Efremov                                        #
#                                                                              #
#   admin/inc/func_files_upload.inc.php                                        #
#   ��������� �� �������� ������ � ��������.                                   #
#                                                                              #
################################################################################
/*

  function upload_simpl_file() �������� ����� ����� ��� ��� ������� �� ���������� ����.
  function upLoadFile()       �������� �������� � ���������� ��������. 
  function fileRename()       ������������ ����� ���� � ��������� ����� ��� ���������� ����� ����.
  
*/

function upload_simple_file ($upload_to_path, $filename_new = '') {
    $errors= '';
    if(isset($_FILES)) {
        foreach ($_FILES as $key => $value) {
            if (is_uploaded_file($_FILES[$key]["tmp_name"])) {
                $filename = $_FILES[$key]["tmp_name"];
                if ($filename_new == '') $filename_new = $filename;
                if (!copy($filename, $upload_to_path.$filename_new)) {
                    $errors .= "����� �� ����������<br>";
                }
            } else {
                $errors .= "����� �� ����������<br>";
            }
        }
    } else {
        $errors .= "������� _FILES �� ����������<br>";
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
            $errors .= '���� "' . $original_filename 
                . '" �� ����������� �� ������!<br>';
            continue;
        }
        
        $size = getimagesize( $tmp_filename );
        if( !$size )
        {
            $errors .= '������ ��� ����������� �������� ����������� � ����� "' 
                . $original_filename . '"!<br>';
            continue;
        }

        // �������������� ������� JPG, GIF, BMP.
        if( !in_array( $size[ 2 ], array( 1, 2, 6 ) ) )
        {
            $errors .= '������ ����� "' . $original_filename 
                . '" ���������� ��� �� ��������������!<br>';
            continue;
        }
                
        if( ( $size[ 0 ] > $max_width ) || ( $size[ 1 ] > $max_height ) ) 
        {
            $errors .= '���� "' . $original_filename 
                . '" ��������� ������������ ������ ' . $max_width . ' x ' 
                . $max_height . ' �����!<br>';
            continue;
        }

        // ������������ ����� ��� �����.
        $new_filename = file_rename( $original_filename, $dir );
        
        // ����������� ���� � ��������� �������.
        if( !move_uploaded_file( $tmp_filename, $dir . $new_filename ) )
        {
            $errors .= '������ ��� ����������� ����� "' . $original_filename 
                . '"!<br>';
            continue;
        }

        // �������� ����������� �� �������.
        $limit_width = isset( $limits[ $key ][ 0 ] ) ? $limits[ $key ][ 0 ] : 0;
        $limit_height = isset( $limits[ $key ][ 1 ] ) ? $limits[ $key ][ 1 ] : 0;
        
        // ��������� ����������� �������������� � ����������� �� ���������
        // ���������� � ��������.
        $ratio = ( $limit_width && $limit_height ) 
            ? min( $size[ 0 ] / $limit_width, $size[ 1 ] / $limit_height )
            : ( $limit_width  ? $size[ 0 ] / $limit_width
            : ( $limit_height ? $size[ 1 ] / $limit_height 
            : 1 ) );

        // ��� ������������� �������� �������.
        if( $ratio != 1 ) 
        {
            resize( $dir . $new_filename, $size[ 0 ] / $ratio, $size[ 1 ] / $ratio );
        }
        
        $files[ $key ] = $new_filename;
    }
    
    if( $files ) return $files;
    
    return false;
}

// ���������, ���������� �� � �������� $dir ���� � ��������� ������,
// � ��� ������������� �������� ��� ����� �� �����������.
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

// �������� ������� ���������� �����.
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
            // ������ ���������, ��� �� ������ � ���� �������.
            $original = '';
        }
    }

    // ���� ������ ����������� ����������� � �������, ���������.    
    if( !$original ) return;

    // ������������� ������� �����������.    
    imagecopyresampled( $resampled, $original, 0, 0, 0, 0, $w, $h, $data[ 0 ], $data[ 1 ] );

    // ������� ��������� �������������� � �������� ����.    
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
