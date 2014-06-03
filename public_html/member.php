<?php
/**
 * This file is part of gamify project.
 * Copyright (C) 2014  Paco Orozco <paco_@_pacoorozco.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * @category   Pakus
 * @package    Member
 * @author     Paco Orozco <paco_@_pacoorozco.info> 
 * @license    http://www.gnu.org/licenses/gpl-2.0.html (GPL v2)
 * @link       https://github.com/pacoorozco/gamify
 */

require_once realpath(dirname(__FILE__) . '/../resources/lib/Bootstrap.class.inc');
\Pakus\Application\Bootstrap::init(APP_BOOTSTRAP_FULL);

// Page only for members
if (!userIsLoggedIn()) {
    // save referrer to $_SESSION['nav'] for redirect after login
    redirect('login.php', $includePreviousURL = true);
}

// Que hem de fer?
$action = getREQUESTVar('a');

// There are some actions that doesn't need header / footer
switch ($action) {
    case 'search':
        // Que hem de buscar?
        $searchterm = getGETVar('q');
        echo getSearchResults($searchterm);
        exit();
        break;
    case 'upload':
        echo uploadProfilePicture();
        exit();
        break;
}

require_once TEMPLATES_PATH . '/tpl_header.inc';

switch ($action) {
    case 'viewuser':
    default:
        $userUUID = getREQUESTVar('item');
        if (empty($userUUID)) {
            // if not suply any user to show, show the own ones
            $userUUID = getUserUUID($_SESSION['member']['id']);
        }
        printProfile($userUUID);
}

require_once TEMPLATES_PATH . '/tpl_footer.inc';
exit();

/*** FUNCTIONS ***/
function getSearchResults($searchterm)
{
        global $db;

        $htmlCode = array();
        $htmlCode[] = '<ul class="list-unstyled list-group">';

        // Nomes farem cerques si busquen mes de tres caracters, aixo evita que sobrecarreguem la BDD
        if ( ! isset($searchterm[3]) ) {
            $htmlCode[] = '<li class="list-group-item list-group-item-info">Tecleja m&eacute;s de 3 car&agrave;cters per fer la cerca</li>';
        } else {
            $query = sprintf("SELECT uuid, username FROM vmembers WHERE username LIKE '%%%s%%'", $db->real_escape_string($searchterm));
            $result = $db->query($query);

            if (0 == $result->num_rows) {
                // No s'ha trobat res
                $htmlCode[] = '<li class="list-group-item list-group-item-danger">No he trobat cap resultat</li>';

            } else {
                // Hem trobat informacio
                while ( $row = $result->fetch_assoc() ) {
                    $htmlCode[] = '<li><a href="member.php?a=viewuser&item=' . $row['uuid'] . '" title="Veure ' . $row['username'] . '" class="list-group-item"><span class="glyphicon glyphicon-user"></span> ' . $row['username'] . "</a></li>";
                }
            }
        }
        $htmlCode[] = '</ul>';

    return implode($htmlCode, PHP_EOL);
}

function printProfile($userUUID)
{
    global $db;

    $query = sprintf("SELECT t1.id, t1.username, t1.total_points, t1.month_points, t1.last_access, t1.level_id, t2.name AS level_name, t2.image AS level_image,t2.experience_needed FROM vmembers AS t1, levels AS t2 WHERE t1.level_id = t2.id AND t1.uuid='%s' LIMIT 1", $userUUID);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    $userId = $row['id'];

    // check if user to view profile is admin
    $admin = userHasPrivileges($userId, 'administrator');

    $query = sprintf("SELECT profile_image FROM members WHERE id='%d' LIMIT 1", $userId);
    $result = $db->query($query);
    $row2 = $result->fetch_assoc();
    $row['profile_image'] = $row2['profile_image'];

    if (false === $admin) {
        $query = sprintf( "SELECT * FROM levels WHERE experience_needed >= '%d' LIMIT 1", $row['total_points']);
        $result = $db->query($query);
        $row2 = $result->fetch_assoc();

        $levelper= round($row['total_points'] / $row2['experience_needed'] * 100);
    }

    ?>
        <div class="row" style="margin-top:50px;">
            <div class="col-md-7">
                <div class="row">
                    <div class="col-md-4">
                        <?php
                        if (empty($row['profile_image'])) {
                            $row['profile_image'] = 'images/default_profile_pic.png';
                        }
                        ?>
                        <img src="<?= $row['profile_image']; ?>" class="img-thumbnail" id="profileImage">
                        <?php
                        if ($userId == $_SESSION['member']['id']) {
                            // L'usuari por editar la seva imatge.
                        ?>
                        <p class="text-center"><a href="#" id="uploadFile" title="Upload"><span class="glyphicon glyphicon-open"></span> Canviar imatge</a></p>
                        <p id="messageBox"></p>
                        <script>
                            var uploadURL = "<?= $_SERVER['PHP_SELF']; ?>?a=upload";
                            head(function () {
                                $(document).ready(function () {
                                    $('a#uploadFile').file();
                                    $('input#uploadFile').file().choose(function (e, input) {
                                        input.upload(uploadURL, function (res) {
                                            if (res=="ERROR") {
                                                $('p#messageBox').attr("class","text-danger");
                                                $('p#messageBox').html("Invalid extension !");
                                            } else {
                                                 $('img#profileImage').attr("src",res);
                                                $('input#profileImageFile').val(res);
                                                $(this).remove();
                                            }
                                        }, '');
                                    } );
                                } );
                            } );
                        </script>

                        <?php
                        }
                        ?>
                    </div>
                    <div class="col-md-8">
                        <p class="h1"><?php echo $row['username']; ?></p>
                        <p class="lead"><?php echo $row['level_name']; ?></p>
                        <p class="small">Darrera connexió el <?php echo strftime('%A, %d de %B', $row['last_access']); ?></p>
                    </div>
                </div>
                <?php
                if (false === $admin) {
                ?>
                <h3>Activitat <small>darrers 10 events</small></h3>
                <?php
                $query = sprintf("SELECT * FROM points WHERE id_member='%d' ORDER BY date DESC LIMIT 10", $userId);
                $result = $db->query($query);
                $htmlCode = array();
                while ( $row3 = $result->fetch_assoc() ) {
                        $htmlCode[] = sprintf ("<p>%s va rebre <strong>%d punts</strong> d'experiència per <em>%s</em></p>",
                                getElapsedTimeString($row3['date']),
                                $row3['points'],
                                $row3['memo']
                                );
                }
                echo implode(PHP_EOL, $htmlCode);
                }
                ?>
            </div>

            <div class="col-md-offset-1 col-md-4">
                <h3>Experiència</h3>
                <div class="media">
                    <img src="<?= getLevelImage($row['level_id']); ?>" width="100" alt="<?php echo $row['level_name']; ?>" class="img-thumbnail media-object pull-left">
                    <div class="media-body">
                        <p class="lead media-heading"><?php echo $row['level_name']; ?></p>
                        <?php
                        if (false === $admin) {
                        ?>
                        <p>Nivell següent</p>
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['total_points']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $row2['experience_needed']; ?>" style="width: <?php echo $levelper; ?>%">
                            <span><?php echo $row['total_points'] . '/' . $row2['experience_needed']; ?></span>
                            </div>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>

                <?php
                if (false === $admin) {
                $query = sprintf("SELECT COUNT(*) AS completed FROM members_badges WHERE id_member='%d' AND status='completed'", $userId);
                $result = $db->query($query);
                $row = $result->fetch_assoc();
                $badges = $row['completed'];

                $query = sprintf("SELECT t1.image, t1.name, t1.description, t1.amount_needed, t2.amount, t2.status FROM badges AS t1, members_badges AS t2 WHERE t2.id_member='%d' AND t1.id=t2.id_badges", $userId);
                $result = $db->query($query);
                echo '<h3>Insígnies ('. $badges .')</h3>';
                $htmlCode = array();
                while ($row = $result->fetch_assoc()) {
                    $progress = '';
                    if ($userId == $_SESSION['member']['id']) {
                        $achievesToBadge = $row['amount_needed'] - $row['amount'];
                        if (($row['amount_needed'] > 1) && ($achievesToBadge > 0)) {
                            $progress = sprintf(
                                "\nMuy bien! Tienes %d %s, sólo te %s %d más.",
                                $row['amount'],
                                ($row['amount'] > 1) ? 'logros' : 'logro',
                                ($achievesToBadge > 1) ? 'faltan' : 'falta',
                                $achievesToBadge
                            );
                        }
                    }
                    $title = sprintf("%s\n%s%s", $row['name'], $row['description'], $progress);
                    $htmlCode[] = '<a href="#" title="' . $title . '">';
                    $image = ('completed' == $row['status']) ? $row['image'] : 'images/default_badge_off.png';
                    $htmlCode[] = '<img src="' . $image .'" alt="'. $row['name'] . '" class="img-thumbnail" width="80">';
                    $htmlCode[] = '</a>';
                }
                echo implode(PHP_EOL, $htmlCode);
                }
                ?>
            </div>
        </div>
    <?php
}

function uploadProfilePicture()
{
    global $CONFIG, $_SESSION, $db;

    # upload the file to the filesystem uploads dir
    $destinationPath = $CONFIG['site']['uploads'] . '/profiles';
    list($returnedValue, $returnedMessage) = uploadFile('uploadFile', $destinationPath);

    if (false === $returnedValue) { return 'ERROR'; }

    // Deletes previous profile picture file
    $query = sprintf("SELECT profile_image FROM members WHERE id='%d'", $_SESSION['member']['id']);
    $result = $db->query($query);
    $row = $result->fetch_assoc();
    if (file_exists($row['profile_image'])) { unlink($row['profile_image']); }

    // ACTION: Si es la primera vegada que puja una imatge... guanya un badge
    if (empty($row['profile_image'])) {
        doSilentAction($_SESSION['member']['id'], 19);
    }
    // END ACTION

    $query = sprintf("UPDATE members SET profile_image='%s' WHERE id='%d'",
            $returnedMessage,
            $_SESSION['member']['id']
            );
    $db->query($query);

    return $returnedMessage;
}
