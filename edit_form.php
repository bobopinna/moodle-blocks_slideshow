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

require_once($CFG->dirroot . '/repository/lib.php');

class block_slideshow_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
        global $CFG, $PAGE, $COURSE;

        $addableslides = 1;

        $mform->addElement('header', 'configslides', get_string('configslides', 'block_slideshow')); 
        $elements = array();
        $elementsoptions = array();
        $elements[] = $mform->createElement('checkbox', 'config_enabled', get_string('configslide', 'block_slideshow'), get_string('configslideenabled', 'block_slideshow'));
        $elementsoptions['config_enabled']['checked'] = false;

        $elements[] = $mform->createElement('date_time_selector', 'config_startslide', get_string('configslidestart', 'block_slideshow'), array('optional' => true));
        $elementsoptions['config_startslide']['disabledif'] = array('enabled', 'notchecked');

        $elements[] = $mform->createElement('date_time_selector', 'config_endslide', get_string('configslideend', 'block_slideshow'), array('optional' => true));
        $elementsoptions['config_endslide']['disabledif'] = array('enabled', 'notchecked');

        $fileoptions = array('subdirs'=>0,
                             'maxfiles'=>1,
                             'maxbytes'=>$COURSE->maxbytes,
                             'accepted_types'=>'web_image',
                             'return_types'=>FILE_INTERNAL);
        $elements[] = $mform->createElement('filemanager', 'config_imageslide', get_string('configslidefile', 'block_slideshow'), null,  $fileoptions);
        $elementsoptions['config_imageslide']['disabledif'] = array('enabled', 'notchecked');

        $elements[] = $mform->createElement('text', 'config_alt', get_string('configslidealt', 'block_slideshow'));
        $elementsoptions['config_alt']['type'] = PARAM_TEXT;
        $elementsoptions['config_alt']['disabledif'] = array('config_enabled', 'notchecked');


        $choices = array();
        $choices['fullsize'] = get_string('fullsize', 'block_slideshow');
        $choices['left'] = get_string('left', 'block_slideshow');
        $choices['center'] = get_string('center', 'block_slideshow');
        $choices['right'] = get_string('right', 'block_slideshow');
        $elements[] = $mform->createElement('select', 'config_imageposition', get_string('configslideimageposition', 'block_slideshow'), $choices);
        $elementsoptions['config_imageposition']['disabledif'] = array('config_enabled', 'notchecked');
        //$elementsoptions['config_imageposition']['default'] = 'fullsize';

        $elements[] = $mform->createElement('text', 'config_title', get_string('configslidetitle', 'block_slideshow'));
        $elementsoptions['config_title']['type'] = PARAM_TEXT;
        $elementsoptions['config_title']['disabledif'] = array('config_enabled', 'notchecked');

        $elements[] = $mform->createElement('textarea', 'config_caption', get_string('configslidecaption', 'block_slideshow'));
        $elementsoptions['config_caption']['type'] = PARAM_TEXT;
        $elementsoptions['config_caption']['disabledif'] = array('config_enabled', 'notchecked');

        $choices = array();
        $choices['topleft'] = get_string('topleft', 'block_slideshow');
        $choices['top'] = get_string('top', 'block_slideshow');
        $choices['topright'] = get_string('topright', 'block_slideshow');
        $choices['left'] = get_string('left', 'block_slideshow');
        $choices['center'] = get_string('center', 'block_slideshow');
        $choices['right'] = get_string('right', 'block_slideshow');
        $choices['bottomleft'] = get_string('bottomleft', 'block_slideshow');
        $choices['bottom'] = get_string('bottom', 'block_slideshow');
        $choices['bottomright'] = get_string('bottomright', 'block_slideshow');
        $choices['fullsize'] = get_string('fullsize', 'block_slideshow');
        $elements[] = $mform->createElement('select', 'config_captionposition', get_string('configslidecaptionposition', 'block_slideshow'), $choices);
        $elementsoptions['config_captionposition']['disabledif'] = array('config_enabled', 'notchecked');
        //$elementsoptions['config_captionposition']['default'] = 'bottomright';

        $elements[] = $mform->createElement('text', 'config_link', get_string('configslidelink', 'block_slideshow'));
        $elementsoptions['config_link']['type'] = PARAM_URL;
        $elementsoptions['config_link']['disabledif'] = array('config_enabled', 'notchecked');

        $elements[] = $mform->createElement('static', 'slideend', null, '<hr />');
     
        $startelements = $addableslides; 
        if (isset($this->block->instance->configdata)) {
            if (isset(unserialize(base64_decode($this->block->instance->configdata))->slides)) {
                $startelements = unserialize(base64_decode($this->block->instance->configdata))->slides + $addableslides;
            }
        }
        $this->repeat_elements($elements, $startelements, $elementsoptions, 'config_slides', 'addslides', $addableslides, get_string('addslides', 'block_slideshow'), true);
        
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        $mform->setExpanded('configheader');

        $choices = array();
        $choices['always'] = get_string('always', 'block_slideshow');
        if ($PAGE->context === context_course::instance(SITEID)) {
            $choices['beforelogin'] = get_string('beforelogin', 'block_slideshow');
            $choices['afterlogin'] = get_string('afterlogin', 'block_slideshow');
        }
        if (count($choices) > 1) {
            $mform->addElement('select', 'config_showslides', get_string('configshowslides', 'block_slideshow'), $choices);
            $mform->setDefault('config_showslides', 'always');
        } else {
            $mform->addElement('hidden', 'config_showslides', 'always');
            $mform->setType('configshowslides', PARAM_TEXT);
        }

        $choices = array();
        for ($i=1; $i<=$mform->getElementValue('config_slides'); $i++) {
            $choices[$i-1] = $i;
        }
        $mform->addElement('select', 'config_firstslide', get_string('configfirstslide', 'block_slideshow'), $choices );
        $mform->setDefault('config_firstslide', 1);

        $choices = array();
        for ($i=1; $i<=60; $i++) {
            $choices[$i] = $i;
        }
        $mform->addElement('select', 'config_interval', get_string('configinterval', 'block_slideshow'), $choices );
        $mform->setType('config_interval', PARAM_INT);
        $mform->setDefault('config_interval', 5);

        $choices = array();
        $choices['fade'] = get_string('fade', 'block_slideshow');
        $choices['slideLeft'] = get_string('slideleft', 'block_slideshow');
        $mform->addElement('select', 'config_transition', get_string('configtransition', 'block_slideshow'), $choices);
        $mform->setDefault('config_transition', 'fade');
        
        $choices = array();
        for ($i=1; $i<=20; $i++) {
            $value =  $i/4;
            $choices["$value"] = $value;
        }
        $mform->addElement('select', 'config_transitionduration', get_string('configtransitionduration', 'block_slideshow'), $choices);
        $mform->setType('config_transitionduration', PARAM_FLOAT);
        $mform->setDefault('config_transitionduration', '0.5');

        $choices = array();
        $choices['topleft'] = get_string('topleft', 'block_slideshow');
        $choices['top'] = get_string('top', 'block_slideshow');
        $choices['topright'] = get_string('topright', 'block_slideshow');
        $choices['left'] = get_string('left', 'block_slideshow');
        $choices['right'] = get_string('right', 'block_slideshow');
        $choices['bottomleft'] = get_string('bottomleft', 'block_slideshow');
        $choices['bottom'] = get_string('bottom', 'block_slideshow');
        $choices['bottomright'] = get_string('bottomright', 'block_slideshow');
        $mform->addElement('select', 'config_pagerposition', get_string('configpagerposition', 'block_slideshow'), $choices);
        $mform->setDefault('config_pagerposition', 'bottomright');
    }

    function set_data($defaults) {
        global $COURSE;

        if ($this->block->user_can_edit()) {
            if  (!empty($this->block->config->slides)) {
                 $slidenumber = $this->block->config->slides;

                 $fileoptions = array('subdirs'=>false,
                                      'maxfiles'=>1,
                                      'maxbytes'=>$COURSE->maxbytes,
                                      'accepted_types'=>'web_image',
                                      'return_types'=>FILE_INTERNAL);

                 $slides = array();
                 for ($i=0; $i<$slidenumber; $i++) {
                     $slides[$i] = new stdClass();
                     if  (!empty($this->block->config->enabled[$i])) {
                          $slides[$i]->enabled = $this->block->config->enabled[$i];
                     }
                     
                     if  (!empty($this->block->config->startslide[$i])) {
                          $slides[$i]->startslide = $this->block->config->startslide[$i];
                     }

                     if  (!empty($this->block->config->endslide[$i])) {
                          $slides[$i]->endslide = $this->block->config->endslide[$i];
                     }

                     if  (!empty($this->block->config->imageslide[$i])) {
                          $draftitemid = '';
                          file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_slideshow', 'slides', $i, $fileoptions);
                          $this->block->config->imageslide[$i] = $draftitemid;
                          $slides[$i]->imageslide = $draftitemid;
                     }

                     if  (!empty($this->block->config->imageposition[$i])) {
                          $slides[$i]->imageposition = $this->block->config->imageposition[$i];
                     }
                     
                     if  (!empty($this->block->config->title[$i])) {
                          $slides[$i]->title = $this->block->config->title[$i];
                     }
                     
                     if  (!empty($this->block->config->caption[$i])) {
                          $slides[$i]->caption = $this->block->config->caption[$i];
                     }
                     
                     if  (!empty($this->block->config->captionposition[$i])) {
                          $slides[$i]->captionposition = $this->block->config->captionposition[$i];
                     }
                     
                     if  (!empty($this->block->config->link[$i])) {
                          $slides[$i]->link = $this->block->config->link[$i];
                     }
                     
                 }
            }
            if  (!empty($this->block->config->showslides)) {
                 $showslides = $this->block->config->showslides;
            }
            if  (!empty($this->block->config->interval)) {
                 $showslides = $this->block->config->interval;
            }
            if  (!empty($this->block->config->firstslide)) {
                 $firstslide = $this->block->config->firstslide;
            }
            if  (!empty($this->block->config->transition)) {
                 $transition = $this->block->config->transition;
            }
            if  (!empty($this->block->config->transitiontime)) {
                 $transition = $this->block->config->transitiontime;
            }
            if  (!empty($this->block->config->pagerposition)) {
                 $transition = $this->block->config->pagerposition;
            }
        }

        parent::set_data($defaults);
/*
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }

        if (isset($slidenumber)) {
            $this->block->config->slides = $slidenumber;
            if (isset($slides)) {
                for ($i=0; $i<$slidenumber; $i++) {
                     if (isset($slides[$i]->enabled)) {
                         $this->block->config->enabled[$i] = $slides[$i]->enabled;
                     }
                     if (isset($slides[$i]->imageslide)) {
                         $this->block->config->imageslide[$i] = $slides[$i]->imageslide;
                     }
                     if (isset($slides[$i]->imageposition)) {
                         $this->block->config->imageposition[$i] = $slides[$i]->imageposition;
                     }
                     if (isset($slides[$i]->title)) {
                         $this->block->config->title[$i] = $slides[$i]->title;
                     }
                     if (isset($slides[$i]->caption)) {
                         $this->block->config->caption[$i] = $slides[$i]->caption;
                     }
                     if (isset($slides[$i]->captionposition)) {
                         $this->block->config->captionposition[$i] = $slides[$i]->captionposition;
                     }
                     if (isset($slides[$i]->link)) {
                         $this->block->config->link[$i] = $slides[$i]->link;
                     }
                }
            }
        }
        if (isset($showslides)) {
            $this->block->config->showslides = $showslides;
        }
        if (isset($firstslide)) {
            $this->block->config->firstslide = $firstslide;
        }
        if (isset($transition)) {
            $this->block->config->transition = $transition;
        }

*/
    }
}
