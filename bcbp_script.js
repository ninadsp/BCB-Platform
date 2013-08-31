jQuery(document).ready(function($){
    
    /**
    * ADMIN PAGE
    */

    /**
    * Setup
    */

    /**
    * Dynamically update the number of track fields in the form
    * based on input
    */
    $("input#num_tracks").change(function(){

        var _this = $(this).val();
        
        var current_length = $(".bcbp_track_container").length;
        if(current_length > _this) {
            for(var i = current_length; i > _this; i--) {
                $(".bcbp_track_container:last").remove();
            }
        }
        else if(current_length < _this) {
            for(var i = current_length; i < _this; i++) {
                var current_row = $(".bcbp_track_container:last").clone();

                current_row.find('input').val('')
                .end().find('label').html('Track ' + (i+1));
                
                current_row.insertAfter($(".bcbp_track_container:last"));
            }
        }
        else {
            console.log("This shouldn't really happen");
        }
    });

    /**
    * Dynamically update the number of session fields in the form
    * based on input
    */
    $("input#num_slots").change(function() {

        var _this = $(this).val();

        var current_length = $(".bcbp_slot_container").length;
        if(current_length > _this) {
            for(var i = current_length; i > _this; i--) {
                $(".bcbp_slot_container:last").remove();
            }
        }
        else if(current_length < _this) {
            for(var i = current_length; i < _this; i++) {
                    var current_row = $(".bcbp_slot_container:last").clone();

                    current_row.find("select[name='slot-select[]']").val('fixed')
                    .end().find("input[name='slot-name[]']").val('')
                    .end().find("input[name='slot-start[]']").val('')
                    .end().find("input[name='slot-end[]']").val('')
                    .end().find('label:first').html('Slot ' + (i+1));

                    current_row.insertAfter($(".bcbp_slot_container:last"));
            }
        }
        else {
            console.log("This shouldn't happen");
        }
    });
    
   /**
   * Schedule
   */
   $("#bcbp_form_post_speaker").submit(function(e){
        e.preventDefault();

        data = {
            action: 'bcbp_schedule_post_speaker',
            speaker_username: $("#speaker_username").val()
        };

        $.post(ajaxurl, data, function (response) {
            console.log(response);
        });
   });
});
