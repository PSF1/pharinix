/* 
 *  Pharinix Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
 * Sources https://github.com/PSF1/pharinix
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
var readInterval = 2000;
var actMons = [];
var dataviewid = '';

function loadStates() {
    var data = {
        command: 'lpReadAll',
        content: '1',
        interface: 'echoJson',
    };
    readInterval = 5000;
    if ($('#lpmviewerswitch').is(':checked')) {
        console.log('llamada');
        apiCall(data, function(mons){
            $('#lpmviewer').empty();
            $.each(mons.monitors, function(i, mon){
                readInterval = 2000;
                updateState(mon);
                actMons.push(mon);
            });
        })
    }
    setTimeout(loadStates, readInterval);
}

function closeMon(id) {
    var data = {
        command: 'lpClose',
        id: id,
        interface: 'nothing',
    };
    apiCall(data);
}

function updateState(mon) {
    var html = '';
    html += '<span><b>'+mon.label+'</b>: '+mon.step+' / '+mon.stepsTotal+' · '+mon.stepLabel+'</span>';
    html += '<div class="progress">';
    var barStyle = 'progress-bar-info';
    if (mon.error) {
        barStyle = 'progress-bar-danger';
    }
    var percent = 100;
    if (mon.stepsTotal != 0) {
        percent = mon.percent;
    }
    html += '<div class="progress-bar active '+barStyle+' progress-bar-striped" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%">';
    if (mon.stepsTotal != 0) {
        html += '<span>'+mon.percent+'%</span>';
    }
    html += '</div>';
    html += '</div>';
    $('#lpmviewer').append(html);
}

$(document).ready(function(){
    dataviewid = $('#lpmviewer').attr('data-viewid');
    setTimeout(loadStates, readInterval);
});