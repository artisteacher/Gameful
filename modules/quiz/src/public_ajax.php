<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 1/11/19
 * Time: 2:19 PM
 */


add_shortcode( 'go_test', 'go_test_shortcode' );
function go_test_shortcode( $atts ) {

    $test_stage_array = unserialize($atts['quiz']);
    $stage = intval($atts['stage']);

    $test_field_input_question = (!empty($test_stage_array[0]) ? $test_stage_array[0] : null);//an array of the questions
    $test_field_input_array = (!empty($test_stage_array[1]) ? $test_stage_array[1] : null);//an array of the answers[0] and the correct answer[1]
    $test_field_select_array = (!empty($test_stage_array[2]) ? $test_stage_array[2] : null);//an array of the type of questions (radio or checkbox)
    $test_field_block_count = (!empty($test_stage_array[3]) ? (int)$test_stage_array[3] : null);//an integer of the number of questions
    $test_field_input_count = (!empty($test_stage_array[4]) ? $test_stage_array[4] : null);//an array of integers of the number of answers

    if ($test_field_block_count > 0) {//if there are questions
        echo "<div id='go_test_container_{$stage}' class='go_test_container'>";
        for ($i = 0; $i < $test_field_block_count; $i++) {//print out at least one question block
            //$correct = (isset($test_field_input_array[$i][1]) ? $test_field_input_array[$i][1] : array());
            $question_type = (isset($test_field_select_array[$i]) ? $test_field_select_array[$i] : 'radio');
            $question = (isset($test_field_input_question[$i]) ? $test_field_input_question[$i] : "");
            $answer_count = (isset($test_field_input_count[$i]) ? $test_field_input_count[$i] : 1);

            $question_num = $i;
            //$answer_array = array();
            echo "
                    <ul id='go_test_{$stage}_{$question_num}'  class='go_test_{$question_num} go_test go_test_list go_test_{$question_type}'>
                        <li>
                            <div style='font-weight:700;'>".ucfirst( $question )."<span class='go_wrong_answer_marker' style='display: none;'>wrong</span><span class='go_correct_answer_marker' style='display: none;'>correct</span></div>
                        </li>";

            for ($x = 0; $x < $answer_count; $x++) {
                $answer = (isset($test_field_input_array[$i][0][$x]) ? $test_field_input_array[$i][0][$x] : null);

                if ($question_type == 'radio') {
                    echo "<li class='go_test go_test_element'><input type='radio' name='go_test_answer_{$question_num}' value='{$answer}'/> {$answer}</li>";
                }else {
                    echo "<li class='go_test go_test_element'><input type='checkbox' name='go_test_answer_{$question_num}_{$x}' value='{$answer}'/>{$answer}</li>";
                }
            }
            echo "</ul>";

        }
        echo "</div>";
    }
    echo "<p id='go_test_error_msg' class='go_error_msg' style='color: red;'></p>";


}
