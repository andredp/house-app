<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{

    const SCENARIO_VALIDATE = 'validate';
    const SCENARIO_LOGIN = 'login';

    public $username;
    public $password;
    public $access_token;
    public $rememberMe = true;

    private $_user = false;

    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            self::SCENARIO_VALIDATE => ['access_token'],
            self::SCENARIO_LOGIN => ['username', 'password']
        ]);
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required on login
            [['username', 'password'], 'required', 'on' => self::SCENARIO_LOGIN],
            ['password', 'validatePassword', 'on' => self::SCENARIO_LOGIN],

            ['access_token', 'required', 'on' => self::SCENARIO_VALIDATE],
            ['access_token', 'validateToken', 'on' => self::SCENARIO_VALIDATE],

            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function validateToken($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addError($attribute, 'Invalid access token.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            if ($this->getScenario() === self::SCENARIO_LOGIN) {
                $this->_user = User::findByUsername($this->username);
            } else {
                $this->_user = User::findIdentityByAccessToken($this->access_token);
            }
        }

        return $this->_user;
    }
}
