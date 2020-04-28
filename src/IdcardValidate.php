<?php

namespace Validator;

use  yii\validators\Validator;

class IdcardValidate extends Validator
{
    /**
     * 中国大陆身份证号码
     *
     * @var string
     */
    protected $idNumber;

    /**
     * 中国大陆身份证号码长度
     *
     * @var int
     */
    protected $idLength;

    /**
     * 加权因子
     *
     * @var array
     */
    protected $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

    /**
     * 校验码
     *
     * @var array
     */
    protected $verifyCode = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

    /**
     * 校验身份证号格式
     *
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $this->idNumber = strtoupper(trim($model->$attribute));

        $this->idLength = strlen($this->idNumber);
        if ($this->checkFormat() && $this->checkBirth() && $this->checkLastCode()) {

            return true;
        }

        return $this->addError($model, $attribute, '身份证号格式错误');
    }

    /**
     * 通过正则表达式检测身份证号码格式
     *
     * @return bool
     */
    protected function checkFormat()
    {
        $this->id15To18();

        if ($this->idLength == 15) {
            $pattern = '/^\d{6}(18|19|20)\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}$/';
        } else {
            $pattern = '/^\d{6}(18|19|20)\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/';
        }

        if (preg_match($pattern, $this->idNumber)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检测身份证生日是否正确
     *
     * @return bool
     */
    protected function checkBirth()
    {
        $year = substr($this->idNumber, 6, 4);
        $month = substr($this->idNumber, 10, 2);
        $day = substr($this->idNumber, 12, 2);

        if (checkdate($month, $day, $year)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 校验身份证号码最后一位校验码
     *
     * @return bool
     */
    protected function checkLastCode()
    {
        if ($this->idLength == 15) {
            return true;
        }

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += substr($this->idNumber, $i, 1) * $this->factor[$i];
        }

        $mod = $sum % 11;

        if ($this->verifyCode[$mod] != substr($this->idNumber, -1)) {
            return false;
        }
        return true;
    }

    /**
     * 将 15 位身份证转化为 18 位身份证号码
     *
     * @return string
     */
    protected function id15To18()
    {
        if ($this->idLength == 15) {
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
            if (array_search(substr($this->idNumber, 12, 3), ['996', '997', '998', '999']) !== false) {
                $this->idNumber = substr($this->idNumber, 0, 6) . '18' . substr($this->idNumber, 6, 9);
            } else {
                $this->idNumber = substr($this->idNumber, 0, 6) . '19' . substr($this->idNumber, 6, 9);
            }

            // 补全最后一位

        }

        return $this->idNumber;
    }
}
