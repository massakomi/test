<?php

class MaterialsDirections extends CActiveRecord
{


    public $groups, $name;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 't_materials_directions';
    }

    /**
     * @return string the associated database table name
     */
    public function getTableName()
    {
        return 't_materials_directions';
    }

/*MaterialsDirections::saveBatch($materialId, $_POST['materials']['directions'])

    public function saveAll($directions, $materialId)
    {
        if ($directions && $materialId > 0) {
            Yii::app()->db->createCommand('delete from t_materials_directions where groupId='.$materialId.'')->query();
            foreach ($directions as $k => $v) {
            	Yii::app()->db->createCommand('insert into t_materials_directions (`groupId`, `materialId`)  VALUES ('.$v.', '.$materialId.')')->query();
            }
        }
    }*/

    public function saveBatch($mId, $groups)
    {
        if ($mId === false) {
            $model = Materials::model()->find(array(
              'select' => 'MAX(id) id',
            ));
            $mId = $model->id;
        }
        if (!$mId) {
            return ;
        }
		$this->dropByUid($mId);
        if (!empty($groups)) {
            foreach ($groups as $group) {
                if (!$group) {
                    continue;
                }
                $tail[] = "('{$mId}','{$group}')";
            }
            if (!$tail) {
                return ;
            }
            yii::app()->db->createCommand("INSERT INTO {$this->tableName} (`materialId`, `groupId`) VALUES " . implode(',', $tail))->execute();
        }
    }

    public function dropByUid($mId)
    {
        yii::app()->db->createCommand("DELETE FROM {$this->tableName} WHERE `materialId`={$mId}")->execute();
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

}
