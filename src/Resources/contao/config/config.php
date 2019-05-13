<?php

declare(strict_types=1);

/*
 * This file is part of the Wangaz\ContaoIsotopeWirecardBundle.
 *
 * (c) Wangaz
 * (c) inspiredminds
 *
 * @license CC-BY-SA-4.0
 */

\Isotope\Model\Payment::registerModelType('wirecard', \Wangaz\ContaoIsotopeWirecardBundle\Isotope\Model\Payment\Wirecard::class);
