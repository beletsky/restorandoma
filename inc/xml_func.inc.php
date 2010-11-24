<?
// ���� ������
define ('IMP_ERR_NOFILE', 10);
define ('IMP_ERR_CREATE', 11);
define ('IMP_ERR_EMPTY', 12);

// ��������� ������ ���������� ������ XML ���������. �� ������ ������ ���������� � ���� ��� ���� � ��� ��������� XML ��������

function getchildren($vals, &$i) {
  $children = array();     // Contains node data

  if (isset($vals[$i]['value']))
    $children['VALUE'] = $vals[$i]['value'];

  /* ���� �� ���� ����� */
  while (++$i < count($vals)) {
    switch ($vals[$i]['type']) {
      case 'cdata':
        if (isset($children['VALUE']))
          $children['VALUE'] .= $vals[$i]['value'];
        else
          $children['VALUE'] = $vals[$i]['value'];
        break;

      case 'complete':
        if (isset($vals[$i]['attributes'])) {
            $children[$vals[$i]['tag']][] = $vals[$i]['attributes'];

            $index = count($children[$vals[$i]['tag']])-1;

            if (isset($vals[$i]['value']))
               $children[$vals[$i]['tag']][$index]['VALUE'] = $vals[$i]['value'];
            else
               $children[$vals[$i]['tag']][$index]['VALUE'] = '';

        } else {

               if (isset($vals[$i]['value']))
                   $children[$vals[$i]['tag']][]['VALUE'] = $vals[$i]['value'];
               else
                   $children[$vals[$i]['tag']][]['VALUE'] = '';
           }
        break;

      /* � ���� ���� ���� */
      case 'open':
        if (isset($vals[$i]['attributes'])) {
          $children[$vals[$i]['tag']][] = $vals[$i]['attributes'];
          $index = count($children[$vals[$i]['tag']])-1;
          $children[$vals[$i]['tag']][$index] = array_merge($children[$vals[$i]['tag']][$index],getchildren($vals, $i));
        } else {
            $children[$vals[$i]['tag']][] = getchildren($vals, $i);
            }
        break;
      /* ����� ���� */
      case 'close':
        return $children;
    }
  }
}

// ������� XML �����. �� ������ ������:
//   error - ����� ������
//   xml   - ������-������, ����������� ��������� XML-�����

function imp_xml_parse($content) {
    $parser = xml_parser_create();
    if ($parser === FALSE) {
        $error = IMP_ERR_CREATE;
    } else {
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 1);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        $vals = array();
        $index = array();
        xml_parse_into_struct($parser, $content, $vals, $index);
        
        $tree = array();
        $i = 0;

        if (isset($vals[$i]['attributes'])) {
            $tree[$vals[$i]['tag']][] = $vals[$i]['attributes'];
            $index = count($tree[$vals[$i]['tag']])-1;
            $tree[$vals[$i]['tag']][$index] =  array_merge($tree[$vals[$i]['tag']][$index], getchildren($vals, $i));
        }
        else {
            $tree[$vals[$i]['tag']][] =getchildren($vals, $i);
            };
        $xml = $tree;
        }
    xml_parser_free($parser);
    return $xml;
}
?>