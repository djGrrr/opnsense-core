<?php

/**
 *    Copyright (C) 2015 Deciso B.V.
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace OPNsense\Core;


class Filtering
{
    protected static $interface_aliases = [];
    protected static $user_aliases = [];

    protected static $friendly_interfaces = [];
    protected static $real_interfaces = [];

    protected static $filter_if_list = [];

    public static function init($FilterIflist)
    {
        self::$interface_aliases = [];
        self::$user_aliases = [];

        self::$friendly_interfaces = [];
        self::$real_interfaces = [];

        self::$filter_if_list = $FilterIflist;
    }

    public static function setupInterfaceAliases()
    {
        $aliases = [];

        $i = 0;
        foreach (self::$filter_if_list as $if => $ifcfg)
        {
            if (!empty($ifcfg['descr']) && !empty($ifcfg['if']))
            {
                $descr = $ifcfg['descr'];
                $int = $ifcfg['if'];

                $alias = 'IF4_'.$descr;
                self::$interface_aliases[$i] = '$'.$alias;
                self::$friendly_interfaces[$if][4] = &self::$interface_aliases[$i];
                self::$real_interfaces[$int][4] = &self::$interface_aliases[$i];
                $aliases[] = $alias.' = "'.$int.'"';
                $i++;

                $stf = isset($ifcfg['type6']) && ($ifcfg['type6'] == '6rd' || $ifcfg['type6'] == '6to4');

                $alias = 'IF6_'.$descr;
                $int6 = ($stf ? $if.'_stf' : $int);
                self::$interface_aliases[$i] = '$'.$alias;
                self::$friendly_interfaces[$if][6] = &self::$interface_aliases[$i];
                self::$real_interfaces[$int][6] = &self::$interface_aliases[$i];
                if ($int6 != $int)
                {
                    self::$real_interfaces[$int6][6] = &self::$interface_aliases[$i];
                }
                $aliases[] = $alias.' = "'.$int6.'"'."\n";
                $i++;
            }
        }

        return implode("\n", $aliases)."\n\n";
    }
}
