<?php
    session_start();
    $imConf = explode('%%',$_POST['imConf']);
    $exConf = explode('%%',$_POST['exConf']);
    $import = ibase_connect($imConf[0],$imConf[1],$imConf[2]);
    $export = ibase_connect($exConf[0],$exConf[1],$exConf[2]);
    $cr = 0;
    foreach ($_SESSION['tfData'] as $tableName=>$tableData) {
        $tNum = $tableData['num'];
        if ($_POST[$tNum]) {
            $selField = '';
            $whereField = '';
            $insField = '';
            $valField = '';
            $fieldsArray = array();
            foreach ($tableData['fields'] as $fieldName=>$fieldData) {
                $fNum = $fieldData['num'];
                if ($_POST['c'.$tNum.$fNum]) {
                    $selPlus = $selField=='' ? $fieldName : ', '.$fieldName;
                    $selField.=$selPlus.$_POST['rf'.$tNum.$fNum].' '.$fieldName;
                    $insField.=$selPlus;
                    if ($_POST['wf'.$tNum.$fNum]) {
                        $wherePlus = $whereField=='' ? $fieldName.' '.$_POST['wf'.$tNum.$fNum]
                                                     : ' and '.$fieldName.' '.$_POST['wf'.$tNum.$fNum];
                        $whereField.=$wherePlus;
                    }
                    $fieldsArray[] = array(fname=>$fieldName, ftype=>$fieldData['ftype']);
                    $valPlus = $valField=='' ? '?' : ', ?';
                    $valField.=$valPlus;
                }
            }
            if ($_POST['rt'.$tNum]) {
                $insQuery = 'execute procedure '.$_POST['rt'.$tNum].' ('.$valField.')';
            } else {
                $insQuery = 'insert into '.$tableName.' ('.$insField.') values ('.$valField.')';
            }
            $pQuery = ibase_prepare($export,$insQuery);
            $query = 'select '.$selField.' from '.$tableName;
            if ($whereField!='') {
                $query.=' where '.$whereField;
            }
            if ($_POST['ot'.$tNum]) {
                $query.=' order by '.$_POST['ot'.$tNum];
            }
            $res = ibase_query($import, $query) or die('select error');
            while ($data = ibase_fetch_assoc($res)) {
                $args = array($pQuery);
                for ($fn=0; $fn<count($fieldsArray);$fn++) {
                    $cData = $data[$fieldsArray[$fn]['fname']];
                    if($fieldsArray[$fn]['ftype']==261) {
                        $binf = ibase_blob_info($import, $cData) or die('error get info iBlob');
                        $bopn = ibase_blob_open($import, $cData) or die('error open iBlob');
                        $bhn = ibase_blob_create($export) or die('error create eBlob');
                        ibase_blob_add($bhn, ibase_blob_get($bopn, $binf[0]));
                        ibase_blob_close($bopn) or die('error close iBlob');
                        $cData = ibase_blob_close($bhn) or die('error close eBlob');
                    }
                    $args[] = $cData;
                }
                call_user_func_array('ibase_execute', $args) or die('insert error');
                $cr++;
            }
            ibase_free_result($res);
            ibase_free_query($pQuery);
        }
    }
    echo 'Всего '.$cr.' записей перенесено';
    ibase_close($import);
    ibase_close($export);

?>
