<?php

/*
 * Copyright 2012 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

final class PhabricatorCountdownViewController
  extends PhabricatorCountdownController {

  private $id;

  public function willProcessRequest(array $data) {
    $this->id = $data['id'];
  }


  public function processRequest() {

    $request = $this->getRequest();
    $user = $request->getUser();
    $timer = id(new PhabricatorTimer())->load($this->id);
    if (!$timer) {
      return new Aphront404Response();
    }

    require_celerity_resource('phabricator-countdown-css');

    $chrome_visible = $request->getBool('chrome', true);
    $chrome_new = $chrome_visible ? false : null;
    $chrome_link = phutil_render_tag(
      'a',
      array(
        'href' => $request->getRequestURI()->alter('chrome', $chrome_new),
        'class' => 'phabricator-timer-chrome-link',
      ),
      $chrome_visible ? 'Disable Chrome' : 'Enable Chrome');

    $container = celerity_generate_unique_node_id();
    $content =
      '<div class="phabricator-timer" id="'.$container.'">
        <h1 class="phabricator-timer-header">'.
          phutil_escape_html($timer->getTitle()).' &middot; '.
          phabricator_datetime($timer->getDatePoint(), $user).
        '</h1>
        <div class="phabricator-timer-pane">
          <table class="phabricator-timer-table">
            <tr>
              <th>Days</th>
              <th>Hours</th>
              <th>Minutes</th>
              <th>Seconds</th>
            </tr>
            <tr>'.
              javelin_render_tag('td',
                array('sigil' => 'phabricator-timer-days'), '').
              javelin_render_tag('td',
                array('sigil' => 'phabricator-timer-hours'), '').
              javelin_render_tag('td',
                array('sigil' => 'phabricator-timer-minutes'), '').
              javelin_render_tag('td',
                array('sigil' => 'phabricator-timer-seconds'), '').
            '</tr>
          </table>
        </div>'.
        $chrome_link.
      '</div>';

    Javelin::initBehavior('countdown-timer', array(
      'timestamp' => $timer->getDatepoint(),
      'container' => $container,
    ));

    $panel = $content;

    return $this->buildStandardPageResponse(
      $panel,
      array(
        'title' => 'Countdown: '.$timer->getTitle(),
        'chrome' => $chrome_visible
      ));
  }

}
