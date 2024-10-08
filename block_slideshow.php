<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing Slideshow block instances.
 *
 * @package   block_slideshow
 * @copyright 2014 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_slideshow extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_slideshow');
    }

    function has_config() {
        return false;
    }

    function applicable_formats() {
        return array('all' => true,
                     'my' => false);
    }

    function instance_allow_multiple() {
        return true;
    }

    function instance_has_config() {
        return false;
    }

    function get_content() {
        global $CFG, $PAGE, $OUTPUT;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->title = '';
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (!isset($this->config)) {
            $this->config = new stdClass();
        }

        if (!isset($this->config->showslides)) {
            $this->config->showslides = 'always';
        }

        if (!isset($this->config->interval)) {
            $this->config->interval = 5;
        }

        if (!isset($this->config->firstslide)) {
            $this->config->firstslide = 1;
        }

        if (!isset($this->config->transition)) {
            $this->config->transition = 'fade';
        }

        if (!isset($this->config->transitionduration)) {
            $this->config->transitionduration = 0.5;
        }

        if (($this->config->showslides == 'beforelogin') && (isloggedin())) {
            return $this->content;
        }

        if (($this->config->showslides == 'afterlogin') && (!isloggedin())) {
            return $this->content;
        }

        $firstslide = 0;
        if (isset($this->config->slides)) {
            $ssid = $this->instance->id;
            $slidesactive = 0;

            $slidestext = '';
            $fs = get_file_storage();
            $now = time();
            for ($i=0; $i<$this->config->slides ; $i++) {
                if (isset($this->config->enabled[$i]) && !empty($this->config->enabled[$i]) &&
                        (!isset($this->config->startslide[$i]) || ($this->config->startslide[$i] < $now)) &&
                        (!isset($this->config->endslide[$i]) || ($this->config->endslide[$i] > $now))) {
                    $imagefiles = $fs->get_area_files($this->context->id, 'block_slideshow', 'slides', $i);
                    // Get file which was uploaded in draft area.
                    $imagefile = null;
                    foreach ($imagefiles as $file) {
                        if (!$file->is_directory()) {
                            $imagefile = clone($file);
                            break;
                        }
                    }

                    if (!empty($this->config->title[$i]) || !empty($this->config->caption[$i]) || !empty($imagefile)) {
                        $slidestext .= html_writer::start_tag('div', array('class' => 'block_slideshow_slide'));
                        if (!empty($imagefile)) {
                            $imagestyle = 'width: 100%';
                            if (isset($this->config->imageposition[$i])) {
                                switch ($this->config->imageposition[$i]) {
                                    case 'fullsize':
                                        $imagestyle = 'width: 100%';
                                    break;
                                    case 'left':
                                        $imagestyle = 'width: 50%; margin-left: 0px';
                                    break;
                                    case 'center':
                                        $imagestyle = 'width: 50%; margin-left: 25%';
                                    break;
                                    case 'right':
                                        $imagestyle = 'width: 50%; margin-left: 50%';
                                    break;
                                }              
                            }
                            $imageurl = moodle_url::make_pluginfile_url($this->context->id, 
                                                                        'block_slideshow',
                                                                        'slides',
                                                                        $i,
                                                                        $imagefile->get_filepath(),
                                                                        $imagefile->get_filename());
                            $imagealt = $this->config->alt[$i];
                            $imagetext = html_writer::empty_tag('img', array('src' => $imageurl, 'alt' => $imagealt));
                            if (!empty($this->config->link[$i])) {
                                $imagetext = html_writer::link(new moodle_url($this->config->link[$i]),  $imagetext);
                            }     
                            $slidestext .= html_writer::tag('div',$imagetext, array('class' => 'block_slideshow_image',
                                                                                    'style' => $imagestyle));
                        }
                        if (!empty($this->config->title[$i]) || !empty($this->config->caption[$i])) {
                            $captionstyle = 'bottom: 1em; right: 1em';
                            if (isset($this->config->captionposition[$i])) {
                                switch ($this->config->captionposition[$i]) {
                                    case 'topleft':
                                        $captionstyle = 'top: 1em; left: 1em; max-width: 48%';
                                    break;
                                    case 'top':
                                        $captionstyle = 'top: 0px; left: 0px; right: 0px';
                                    break;
                                    case 'topright':
                                        $captionstyle = 'top: 1em; right: 1em; max-width: 48%';
                                    break;
                                    case 'left':
                                        $captionstyle = 'left: 0px; top: 0px; bottom: 0px; max-width: 33%';
                                    break;
                                    case 'center':
                                        $captionstyle = 'top: 0px; bottom: 0px; left: 33%; right: 33%; max-width: 33%';
                                    break;
                                    case 'right':
                                        $captionstyle = 'right: 0px; top: 0px; bottom: 0px; max-width: 33%';
                                    break;
                                    case 'bottomleft':
                                        $captionstyle = 'bottom: 1em; left: 1em; max-width: 48%';
                                    break;
                                    case 'bottom':
                                        $captionstyle = 'bottom: 0px; left: 0px; right: 0px';
                                    break;
                                    case 'bottomright':
                                        $captionstyle = 'bottom: 1em; right: 1em; max-width: 48%';
                                    break;
                                    case 'fullsize':
                                        $captionstyle = 'top: 0px; bottom: 0px; left: 0px; right: 0px';
                                    break;
                                }
                            }

                            $slidestext .= html_writer::start_tag('div', array('class' => 'block_slideshow_caption',
                                                                               'style' => $captionstyle));
                            if (!empty($this->config->title[$i])) {
                                $titletext = $this->config->title[$i];
                                if (!empty($this->config->link[$i])) {
                                    $titletext = html_writer::link(new moodle_url($this->config->link[$i]),  $titletext);
                                }
                                $slidestext .= html_writer::tag('h2', $titletext, array('class' => 'block_slideshow_captiontitle'));
                            }
                            if (!empty($this->config->caption[$i])) {
                                $captiontext = $this->config->caption[$i];
                                if (!empty($this->config->link[$i])) {
                                    $captiontext = html_writer::link(new moodle_url($this->config->link[$i]),  $captiontext);
                                }
                                $slidestext .= html_writer::tag('p', $captiontext, array('class' => 'block_slideshow_captiontext'));
                            }
                            $slidestext .= html_writer::end_tag('div');
                        }
                        $slidestext .= html_writer::end_tag('div');
                        if ($this->config->firstslide == $i) {
                            $firstslide = $slidesactive;
                        }
                        $slidesactive++;
                    }
                }
            }

            if ($slidesactive) {
                $this->content->text .= html_writer::start_tag('div', array('class' => 'block_slideshow_slideshow'));
                $this->content->text .= html_writer::start_tag('div', array('id' => 'block_slideshow_'.$ssid,
                                                                            'class' => 'block_slideshow_slides'));
                $this->content->text .= $slidestext;
                $this->content->text .= html_writer::end_tag('div');
                $params = array();
                if ($slidesactive > 1) {
                    $transition = 'fade';
                    if (isset($this->config->transition)) {
                        $transition = $this->config->transition;
                    }

                    $params['initialSlide'] = $firstslide;
                    $params['autoplay'] = true;
                    $params['autoplaySpeed'] = $this->config->interval * 1000;
                    $params['speed'] = $this->config->transitionduration * 1000;
                    $params['dots'] = true;
                    $params['dotsClass'] = 'slick-dots slick-dots-' . $this->config->pagerposition;
                    $params['infinite'] = true;
                    switch ($transition) {
                        case 'fade':
                            $params['fade'] = true;
                            $params['cssEase'] = 'linear';
                        break;
                        case 'slideRight':
                            $params['rtl'] = true;
                        break;
                        case 'slideUp':
                            $params['vertical'] = true;
                            $params['verticalSwiping'] = true;
                        case 'slideDown':
                            $params['rtl'] = true;
                        break;
                    }
                }

                $PAGE->requires->css('/blocks/slideshow/css/slick.css');
                $PAGE->requires->css('/blocks/slideshow/css/slick-theme.css');
                $PAGE->requires->js_call_amd('block_slideshow/slideshow', 'init', array($params));
                $this->content->text .= html_writer::end_tag('div');

                $this->content->text .= html_writer::start_tag('noscript');
                $this->content->text .= html_writer::tag('span', get_string('enablejavascriptformore', 'block_slideshow'));
                $this->content->text .= html_writer::end_tag('noscript');
            }
        } else {
            $this->content->text = '';
        }

        return $this->content;
    }


    /**
     * Serialize and store config data
     */

    function instance_config_save($data, $nolongerused = false) {
        global $USER, $COURSE;

        $config = new stdClass();
        foreach ($data as $fieldname => $fieldvalue) {
             if (is_array($fieldvalue)) {
                 $config->{$fieldname} = array();
             } else {
                 $config->{$fieldname} = $fieldvalue;
             }
        }
        $fileoptions = array('subdirs'=>false,
                             'maxfiles'=>1,
                             'maxbytes'=>$COURSE->maxbytes,
                             'accepted_types'=>'web_image',
                             'return_types'=>FILE_INTERNAL);

        $saved = 0;
        for ($i=0; $i<$data->slides; $i++) {
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->imageslide[$i], 'id');
            if ((count($draftfiles) > 1)
                   ||(isset($data->title[$i]) && !empty($data->title[$i]))
                   || (isset($data->caption[$i]) && !empty($data->caption[$i]))
                   || (isset($data->link[$i]) && !empty($data->link[$i]))) {

                $config->enabled[$saved] = 0;
                if (isset($data->enabled[$i]) && !empty($data->enabled[$i])) {
                    $config->enabled[$saved] = $data->enabled[$i];
                    if ($data->firstslide >= $saved) {
                        $config->firstslide = $saved;
                    }
                }
                if (isset($data->startslide[$i]) && !empty($data->startslide[$i])) {
                    $config->startslide[$saved] = $data->startslide[$i];
                }
                if (isset($data->endslide[$i]) && !empty($data->endslide[$i])) {
                    $config->endslide[$saved] = $data->endslide[$i];
                }
                if (isset($data->imageslide[$i]) && !empty($data->imageslide[$i])) {
                    $config->imageslide[$saved] = $data->imageslide[$i];
                    file_save_draft_area_files($config->imageslide[$saved],
                                               $this->context->id,
                                               'block_slideshow',
                                               'slides',
                                               $saved,
                                               $fileoptions);
                }
                $config->alt[$saved] = isset($data->alt[$i]) && !empty($data->alt[$i])?$data->alt[$i]:'';
                $config->imageposition[$saved] = isset($data->imageposition[$i]) && !empty($data->imageposition[$i])
                                                 ?$data->imageposition[$i]
                                                 :'';
                $config->title[$saved] = isset($data->title[$i]) && !empty($data->title[$i])?$data->title[$i]:'';
                $config->caption[$saved] = isset($data->caption[$i]) && !empty($data->caption[$i])?$data->caption[$i]:'';
                $config->captionposition[$saved] = isset($data->captionposition[$i]) && !empty($data->captionposition[$i])
                                                   ?$data->captionposition[$i]
                                                   :'';
                $config->link[$saved] = isset($data->link[$i]) && !empty($data->link[$i])?$data->link[$i]:'';
                $saved++;
                
            }
        }
        for ($i=$saved; $i<$data->slides; $i++) {
            file_save_draft_area_files($data->imageslide[$i],
                                       $this->context->id,
                                       'block_slideshow',
                                       'slides',
                                       $i,
                                       $fileoptions);
        }
        $config->slides = $saved;

        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_slideshow');
        return true;
    }

    function content_is_trusted() {
        return true;
    }

    /**
     * The block should not be dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }

}
