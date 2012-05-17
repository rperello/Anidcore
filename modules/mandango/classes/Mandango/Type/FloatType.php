<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Type;

/**
 * FloatType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class FloatType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toMongo($value)
    {
        return (float) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value)
    {
        return (float) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toMongoInString()
    {
        return '%to% = (float) %from%;';
    }

    /**
     * {@inheritdoc}
     */
    public function toPHPInString()
    {
        return '%to% = (float) %from%;';
    }
}
