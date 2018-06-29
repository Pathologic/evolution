@extends('manager::template.page')
@section('content')
    <?php /*include_once evolutionCMS()->get('ManagerTheme')->getFileProcessor("actions/resources.static.php");*/ ?>
    <?php
    /**
     * Don't delete this file
     * Don't rename and move functions
     */

    $tpl = array(
        'panelHeading' => file_get_contents(MODX_MANAGER_PATH . 'actions/resources/tpl_panelHeading.tpl')
    );

    /**
     * @param string $tpl
     * @param array $ph
     * @return string
     */
    function parsePh($tpl, $ph) {
        $modx = evolutionCMS();
        $_lang = ManagerTheme::getLexicon();

        $tpl = $modx->parseText($tpl, $_lang, '[%', '%]');
        return $modx->parseText($tpl, $ph);
    }

    /**
     * @param array $row
     * @param string $resourceTable
     * @param EvolutionCMS\Legacy\mgrResources $resources
     * @return array
     */
    function prepareElementRowPh($row, $resourceTable, $resources) {
        global $_style;
        $modx = evolutionCMS();
        $_lang = ManagerTheme::getLexicon();

        $types = isset($resources->types[$resourceTable]) ? $resources->types[$resourceTable] : false;

        $_lang["confirm_delete"] = $_lang["delete"];

        switch($resourceTable){
            case 'site_templates':
                $class = $row['selectable'] ? '' : 'disabledPlugin';
                $lockElementType = 1;
                $_lang["confirm_delete"] = $_lang["confirm_delete_template"];
                break;
            case 'site_tmplvars':
                $class = $row['reltpl'] ? '' : 'disabledPlugin';
                $lockElementType = 2;
                $_lang["confirm_delete"] = $_lang["confirm_delete_tmplvars"];
                break;
            case 'site_htmlsnippets':
                $class = $row['disabled'] ? 'disabledPlugin' : '';
                $lockElementType = 3;
                $_lang["confirm_delete"] = $_lang["confirm_delete_htmlsnippet"];
                break;
            case 'site_snippets':
                $class = $row['disabled'] ? 'disabledPlugin' : '';
                $lockElementType = 4;
                $_lang["confirm_delete"] = $_lang["confirm_delete_snippet"];
                break;
            case 'site_plugins':
                $class = $row['disabled'] ? 'disabledPlugin' : '';
                $lockElementType = 5;
                $_lang["confirm_delete"] = $_lang["confirm_delete_plugin"];
                break;
            case 'site_modules':
                $class = $row['disabled'] ? '' : 'disabledPlugin';
                $_lang["confirm_delete"] = $_lang["confirm_delete_module"];
                break;
            default:
                return array();
        }

        // Prepare displaying user-locks
        $lockedByUser = '';
        $rowLock = $modx->elementIsLocked($lockElementType, $row['id'], true);
        if($rowLock && $modx->hasPermission('display_locks')) {
            if($rowLock['sid'] == $modx->sid) {
                $title = $modx->parseText($_lang["lock_element_editing"], array(
                    'element_type' => $_lang["lock_element_type_" . $lockElementType],
                    'lasthit_df' => $rowLock['lasthit_df']
                ));
                $lockedByUser = '<span title="' . $title . '" class="editResource" style="cursor:context-menu;">' . $_style['tree_preview_resource'] . '</span>&nbsp;';
            } else {
                $title = $modx->parseText($_lang["lock_element_locked_by"], array(
                    'element_type' => $_lang["lock_element_type_" . $lockElementType],
                    'username' => $rowLock['username'],
                    'lasthit_df' => $rowLock['lasthit_df']
                ));
                if($modx->hasPermission('remove_locks')) {
                    $lockedByUser = '<a href="javascript:;" onclick="unlockElement(' . $lockElementType . ', ' . $row['id'] . ', this);return false;" title="' . $title . '" class="lockedResource"><i class="' . $_style['icons_secured'] . '"></i></a>';
                } else {
                    $lockedByUser = '<span title="' . $title . '" class="lockedResource" style="cursor:context-menu;"><i class="' . $_style['icons_secured'] . '"></i></span>';
                }
            }
        }
        if($lockedByUser) {
            $lockedByUser = '<div class="lockCell">' . $lockedByUser . '</div>';
        }

        // Caption
        if($resourceTable == 'site_tmplvars') {
            $caption = !empty($row['description']) ? ' ' . $row['caption'] . ' &nbsp; <small>(' . $row['description'] . ')</small>' : ' ' . $row['caption'];
        } else {
            $caption = !empty($row['description']) ? ' ' . $row['description'] : '';
        }

        // Special marks
        $tplInfo = array();
        if($row['locked']) {
            $tplInfo[] = $_lang['locked'];
        }
        if($row['id'] == $modx->config['default_template'] && $resourceTable == 'site_templates') {
            $tplInfo[] = $_lang['defaulttemplate_title'];
        }
        $marks = !empty($tplInfo) ? ' <em>(' . implode(', ', $tplInfo) . ')</em>' : '';

        /* row buttons */
        $buttons = '';
        if($modx->hasPermission($types['actions']['edit'][1])) {
            $buttons .= '<li><a title="' . $_lang["edit_resource"] . '" href="index.php?a=' . $types['actions']['edit'][0] . '&amp;id=' . $row['id'] . '"><i class="fa fa-edit fa-fw"></i></a></li>';
        }
        if($modx->hasPermission($types['actions']['duplicate'][1])) {
            $buttons .= '<li><a onclick="return confirm(\'' . $_lang["confirm_duplicate_record"] . '\')" title="' . $_lang["resource_duplicate"] . '" href="index.php?a=' . $types['actions']['duplicate'][0] . '&amp;id=' . $row['id'] . '"><i class="fa fa-clone fa-fw"></i></a></li>';
        }
        if($modx->hasPermission($types['actions']['remove'][1])) {
            $buttons .= '<li><a onclick="return confirm(\'' . $_lang["confirm_delete"] . '\')" title="' . $_lang["delete"] . '" href="index.php?a=' . $types['actions']['remove'][0] . '&amp;id=' . $row['id'] . '"><i class="fa fa-trash fa-fw"></i></a></li>';
        }
        $buttons = $buttons ? '<div class="btnCell"><ul class="elements_buttonbar">' . $buttons . '</ul></div>' : '';

        $catid = $row['catid'] ? $row['catid'] : 0;

        // Placeholders for elements-row
        return array(
            'class' => $class ? ' class="' . $class . '"' : '',
            'lockedByUser' => $lockedByUser,
            'name' => $row['name'],
            'caption' => $caption,
            'buttons' => $buttons,
            'marks' => $marks,
            'id' => $row['id'],
            'resourceTable' => $resourceTable,
            'actionEdit' => $types['actions']['edit'][0],
            'catid' => $catid,
            'textdir' => ManagerTheme::getTextDir('&rlm;'),
        );
    }

    $resources = new EvolutionCMS\Legacy\mgrResources();

    // Prepare lang-strings for "Lock Elements"
    $unlockTranslations = array(
        'msg' => $_lang["unlock_element_id_warning"],
        'type1' => $_lang["lock_element_type_1"],
        'type2' => $_lang["lock_element_type_2"],
        'type3' => $_lang["lock_element_type_3"],
        'type4' => $_lang["lock_element_type_4"],
        'type5' => $_lang["lock_element_type_5"],
        'type6' => $_lang["lock_element_type_6"],
        'type7' => $_lang["lock_element_type_7"],
        'type8' => $_lang["lock_element_type_8"]
    );
    foreach($unlockTranslations as $key => $value) $unlockTranslations[$key] = iconv($modx->config["modx_charset"], "utf-8", $value);

    // Prepare lang-strings for mgrResAction()
    $mraTranslations = array(
        'create_new' => $_lang["create_new"],
        'edit' => $_lang["edit"],
        'duplicate' => $_lang["duplicate"],
        'remove' => $_lang["remove"],
        'confirm_duplicate_record' => $_lang["confirm_duplicate_record"],
        'confirm_delete_template' => $_lang["confirm_delete_template"],
        'confirm_delete_tmplvars' => $_lang["confirm_delete_tmplvars"],
        'confirm_delete_htmlsnippet' => $_lang["confirm_delete_htmlsnippet"],
        'confirm_delete_snippet' => $_lang["confirm_delete_htmlsnippet"],
        'confirm_delete_plugin' => $_lang["confirm_delete_plugin"],
        'confirm_delete_module' => $_lang["confirm_delete_module"],
    );
    foreach($mraTranslations as $key => $value) {
        $mraTranslations[$key] = iconv($modx->config["modx_charset"], "utf-8", $value);
    }
    ?>

    <script>var trans = '{{ json_encode($unlockTranslations) }}';</script>
    <script>var mraTrans = '{{ json_encode($mraTranslations) }}';</script>

    <script type="text/javascript" src="media/script/jquery.quicksearch.js"></script>
    <script type="text/javascript" src="media/script/jquery.nucontextmenu.js"></script>
    <script type="text/javascript" src="media/script/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="actions/resources/functions.js"></script>

    <h1>
        <i class="fa fa-th"></i><?php echo $_lang['element_management']; ?>
    </h1>

    <div class="sectionBody">
        <div class="tab-pane" id="resourcesPane">
            <script type="text/javascript">
                tpResources = new WebFXTabPane(document.getElementById("resourcesPane"), true);
            </script>
            @include('manager::page.resources.templates', compact('resources', 'tpl'))
            @include('manager::page.resources.tv', compact('resources', 'tpl'))
            @include('manager::page.resources.chunks', compact('resources', 'tpl'))
            @include('manager::page.resources.snippets', compact('resources', 'tpl'))
            @include('manager::page.resources.plugins', compact('resources', 'tpl'))
            @include('manager::page.resources.category', compact('resources', 'tpl'))

            @if(is_numeric($_GET['tab']))
                <script type="text/javascript"> tpResources.setSelectedIndex({{ $_GET['tab'] }});</script>
            @endif
        </div>
    </div>
@endsection
