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

namespace giacomofurlan\slim3AnnotationRouter;

class Argument
{
    /** @var string $name */
    private $name;
    /** @var string $class */
    private $class;

    /**
     * Argument constructor.
     * @param string $name
     * @param string|null $class
     */
    public function __construct($name, $class)
    {
        $this->name = $name;
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
}

