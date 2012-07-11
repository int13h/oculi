//
//
// This file is part of oculi
//
// Copyright (C) 2011-2012, Paul Halliday <paul.halliday@gmail.com>
//                          Sacha Evans <sacha.evans@nscc.ca>
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
//
//


// Is the client outside of our network?
functions chkLIP($x) {



}

// Validate host naming
function chkHostname($x) {
    $result = 0;

    $chk00 = array('AK','AM','AN','DG','BU','CE','CO','CU','DW','DI','IN','KI',
                'LU','MA','PI','SF','SH','ST','DW');

    $chk01 = array('LT','MD','ML','PR','PL','SC','WS','WST');


    $result += count($chk00[substr($x, 0, 2)]);
    $result += count($chk01[substr($x, 2, 4)]);    

    echo $result;


}
