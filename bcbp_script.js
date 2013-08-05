jQuery(document).ready(function($){
    
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
    
    
});
