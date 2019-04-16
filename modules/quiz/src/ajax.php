<?php
/**
 * Created by PhpStorm.
 * User: mmcmurray
 * Date: 12/22/18
 * Time: 6:11 AM
 */


/**
 * checks the test answers
 *
 */
function go_check_quiz_answers() {
   //global $wpdb;

    //check_ajax_referer( 'go_check_quiz_answers' );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_check_quiz_answers' ) ) {
        echo "refresh";
        die( );
    }

    $task_id = (isset($_POST['task_id']) ?  intval($_POST['task_id']) : 0);
    $user_id = (isset( $_POST['user_id'] ) ? intval($_POST['user_id']) : 0 );

    $db_status = go_get_status($task_id, $user_id);
    $status        = (isset( $_POST['status'] ) ? (int) $_POST['status'] : 0 ); // Task's status posted from ajax function
    if ($status != $db_status){
        echo "refresh";
        die();
    }
    $current_stage = $status+1;

    $all_test_choices = (isset($_POST['chosen_answer']) ?  $_POST['chosen_answer'] : array());

    $custom_fields = get_post_custom( $task_id );
    $test_stage = 'go_stages_' . $status . '_quiz';
    //$test_fail_name = 'test_fail_count';


    $test_c_array = $custom_fields[ $test_stage ][0];
    $temp_uns = unserialize( $test_c_array );

    $test_field_input_array = (!empty($temp_uns[1]) ? $temp_uns[1] : null);//an array of the answers[0] and the correct answer[1]
    $test_field_select_array = (!empty($temp_uns[2]) ? $temp_uns[2] : null);//an array of the type of questions (radio or checkbox)
    $num_questions = (!empty($temp_uns[3]) ? (int)$temp_uns[3] : null);//an integer of the number of questions

    $fail_question_ids = array();
    $total_matches = 0;

    //for each question
    //check each answer
    //if correct
    //add 1 to correct count
    //if wrong
    //add 1 to fail count
    //add question 1 to fail array

    //update fail count
    //return true if all correct


    if ($num_questions > 0) {//if there are questions
        for ($i = 0; $i < $num_questions; $i++) {//for each question
            $correct = (isset($test_field_input_array[$i][1]) ? $test_field_input_array[$i][1] : array());
            $question_type = (isset($test_field_select_array[$i]) ? $test_field_select_array[$i] : 'radio');
                if ($question_type == 'radio') {
                    if ($correct[0] == $all_test_choices[$i]){
                        $total_matches++;
                    }else{
                        $fail_question_ids[] = "go_test_{$current_stage}_{$i}";
                    }
                }else {//this is a multi-select
                    $checkbox = 0;
                    $count = count($correct);//the count of the number that need to be checked
                    $correct = array_values($correct);
                    for ($a = 0; $a < $count; $a++) {
                        $answer = (isset($all_test_choices[$i][$a]) ?  $all_test_choices[$i][$a] : null);
                        if (in_array( $answer ,$correct) ){
                            $checkbox++;
                        }
                    }
                    if ($checkbox == $count){
                        $total_matches++;
                    }else{
                        $fail_question_ids[] = "go_test_{$current_stage}_{$i}";
                    }
                }
        }
    }

    if ($total_matches == $num_questions ){
        echo true;
    }else {
        $fail_count = count($fail_question_ids);
        go_update_fail_count($user_id, $task_id, $fail_count, $status, $num_questions);
        echo json_encode($fail_question_ids);
    }

    die();



}

function go_save_quiz_result() {
    //global $wpdb;

    //check_ajax_referer( 'go_save_quiz_result' . $task_id . '_' . $user_id );
    if ( ! wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'go_save_quiz_result' ) ) {
        echo "refresh";
        die( );
    }
    $status        = (isset( $_POST['status'] ) ? (int) $_POST['status'] : 0 ); // Task's status posted from ajax function
    $task_id = (isset($_POST['task_id']) ?  intval($_POST['task_id']) : 0);
    $user_id = (isset( $_POST['user_id'] ) ? intval($_POST['user_id']) : 0 );


    global $wpdb;
    $go_actions_table_name = "{$wpdb->prefix}go_actions";
    $html = (isset($_POST['html']) ?  $_POST['html'] : '');
    //check to see if a quiz-mod exists for this stage
        go_update_actions($user_id, 'quiz_result', $task_id, $status + 1, null, null, $html, null, null, null, null, null, null, null, null, null, null, null, false);
        //  go_update_actions($user_id, $type,              $source_id,     $status,        $bonus_status, $check_type, $result, $quiz_mod, $late_mod, $timer_mod, $global_mod, $xp, $gold, $health, $badge_ids, $group_ids, $notify, $debt)


    die();



}



//Adds the quiz modifier to the actions table
/**
 * @param $user_id
 * @param $task_id
 * @param $fail_count
 * @param $status
 */
function go_update_fail_count($user_id, $task_id, $fail_count, $status, $total_questions){
    global $wpdb;
    $go_actions_table_name = "{$wpdb->prefix}go_actions";
    //$html = (isset($_POST['html']) ?  $_POST['html'] : '');
    //check to see if a quiz-mod exists for this stage
    $quiz_mod_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id 
				FROM {$go_actions_table_name} 
				WHERE source_id = %d AND uid = %d AND stage = %d AND action_type = %s",
            $task_id,
            $user_id,
            $status+ 1,
            'quiz_mod'
        )
    );
    if ($quiz_mod_exists == null) {
        //then update if needed
        go_update_actions($user_id, 'quiz_mod', $task_id, $status + 1, null, $total_questions, $fail_count, null, null, null, null, null, null, null, null, null, null, null, false);
    //  go_update_actions($user_id, $type,              $source_id,     $status,        $bonus_status, $check_type, $result, $quiz_mod, $late_mod, $timer_mod, $global_mod, $xp, $gold, $health, $badge_ids, $group_ids, $notify, $debt)
    }
}

