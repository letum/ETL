<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script src="js/prototype.js"></script>
        <script src="js/main.js"></script>
    </head>
    <body>
        <?php
          session_start();
          echo '<script type="text/javascript">
                var sid = '."'".session_name().'='.session_id()."'".';
                </script>';
          $aConf = file('config.ini');
          $imConf = explode('%%',$aConf[0]);
          $exConf = explode('%%',$aConf[1]);
          echo '<form id="info">
                  <input type="text" size="150" value="'.$aConf[0].'" name="imConf"><br>
                  <input type="text" size="150" value="'.$aConf[1].'" name="exConf"><br>
                  <input type="button" onClick="startTransfer()" value="Начать">
                </form>';
          $import = ibase_connect($imConf[0],$imConf[1],$imConf[2]);
          /* $query = 'select RDB$RELATION_NAME tName from RDB$RELATIONS
                    where RDB$SYSTEM_FLAG = 0
                    order by RDB$RELATION_NAME'; */
          $query = 'select R.RDB$RELATION_NAME tName, R.RDB$FIELD_NAME fName, F.RDB$FIELD_TYPE fType
                    from RDB$FIELDS F, RDB$RELATION_FIELDS R
                    where F.RDB$FIELD_NAME = R.RDB$FIELD_SOURCE and R.RDB$SYSTEM_FLAG = 0
                    order by R.RDB$RELATION_NAME, R.RDB$FIELD_POSITION';
          $res = ibase_query($import,$query);
          $i = 0;
          $tableName = '';
          echo '<div id="res"></div>';
          echo '<form id="DB"><table border="0" cellpadding="5">';
          echo '<tr><th>№</th>
                    <th>ЧБ</th>
                    <th>Таблица</th>
                    <th>Поле</th>
                    <th>Правило для таблицы</th>
                    <th>Правило для поля</th>
                    <th>Условие выборки</th></tr>';
          while ($data = ibase_fetch_assoc($res)) {
              echo '<tr>';
              if (trim($data['TNAME'])!=$tableName) {
                  $i++;
                  $tableName=trim($data['TNAME']);
                  $rezArr[$tableName] = array(num => $i, fields => array());
                  echo '<td>'.$i.'</td>';
                  echo '<td><input type="checkbox" name="'.$i.'"></td>';
                  echo '<td>'.$data['TNAME'].'</td>';
                  echo '<td>order by <input type="text" name="ot'.$i.'"></td>';
                  echo '<td>ХП - <input type="text" name="rt'.$i.'"></td>';
                  echo '<td></td>';
                  echo '<td></td>';
                  echo '</tr><tr>';
                  $j = 0;
              }
              $j++;
              $rezArr[$tableName]['fields'][trim($data['FNAME'])] = array(num=>$j, ftype=>$data['FTYPE']);
              echo '<td>.'.$j.'</td>';
              echo '<td> - </td>';
              echo '<td align="center"><input type="checkbox" name="c'.$i.$j.'">---></td>';
              echo '<td>'.$data['FNAME'].'</td>';
              echo '<td></td>';
              if (in_array($data['FTYPE'],array(7,8,10,11,27))) {
                  echo '<td><input type="text" name="rf'.$i.$j.'"><br>[+,-,*,/]</td>';
                  echo '<td><input type="text" name="wf'.$i.$j.'"><br>[<>,<,>,=,{<,>}=,is null,is not null]</td>';
              } elseif (in_array($data['FTYPE'],array(37))) {
                  echo '<td><input type="text" name="rf'.$i.$j.'"><br>[||,<||]</td>';
                  echo '<td><input type="text" name="wf'.$i.$j.'"><br>[<>,=,LIKE,is null,is not null]</td>';
              } elseif (in_array($data['FTYPE'],array(12,13,35))) {
                  echo '<td><input type="text" name="rf'.$i.$j.'"><br>[-,+]</td>';
                  echo '<td><input type="text" name="wf'.$i.$j.'"><br>[<>,=,is null,is not null]</td>';
              } else {
                  echo '<td><input type="text" name="rf'.$i.$j.'"><br>[]</td>';
                  echo '<td><input type="text" name="wf'.$i.$j.'"><br>[is null,is not null]</td>';
              }
              echo '</tr>';
          }
          echo '</table></form>';
          ibase_free_result($res);
          ibase_close($import);
          
          $_SESSION['tfData'] = $rezArr;
        ?>
    </body>
</html>
