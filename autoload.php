<?php

/**
 * This file is part of slim3AnnotationRouter.
 *
 * slim3AnnotationRouter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * slim3AnnotationRouter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with slim3AnnotationRouter.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * PSR-4 autoload
 * giacomofurlan\slim3AnnotationRouter => src
 *
 * Use if not using composer's
 */
spl_autoload_register(function ($required) {
    if (!preg_match('/^giacomofurlan\\\slim3AnnotationRouter\\\/', $required)) {
        return;
    }

    preg_match('/^giacomofurlan\\\slim3AnnotationRouter\\\(.*)/', $required, $match);
    $searched = explode('\\', $match[1]);

    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src'
        . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $searched) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
