<?php
/*!
 * Radium
 * Copyright 2011-2014 Jack Polgar
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Radium\Database\Model;

use Radium\Hook;

/**
 * Secure password model trait.
 *
 * @package Radium\Database\Model
 * @since 2.0
 * @author Jack Polgar
 */
trait SecurePassword
{
    /**
     * Crypts the users password.
     */
    public function preparePassword()
    {
        $this->{$this->securePasswordField} = crypt(
            $this->{$this->securePasswordField},
            '$2a$10$' . sha1(microtime() . $this->username . $this->email) . '$'
        );
    }

    /**
     * Authenticates the password with the users current password.
     *
     * @param string $password
     *
     * @return boolean
     */
    public function authenticate($password)
    {
        return $this->password === crypt($password, $this->password);
    }

    /**
     * Sets and crypts the new password.
     *
     * @param string $newPassword
     */
    public function setPassword($newPassword)
    {
        $this->password = $newPassword;
        $this->preparePassword();
    }
}
