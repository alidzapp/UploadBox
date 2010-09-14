var CommentsControl = {

	previousFormShown	: null,

	mouseOver	: function(hover_control) {
		var hover = document.getElementById(hover_control);
		if (hover) {
			hover.style.display = 'block';
		}
	},

	mouseOut	: function(hover_control) {
		var hover = document.getElementById(hover_control);
		if (hover) {
			hover.style.display = 'none';
		}
	},

	reply		: function(form_wrapper_control, parent_comment_holder, comment_id) {
		var form_wrapper = document.getElementById(form_wrapper_control);
		if (CommentsControl.previousFormShown) {
			CommentsControl.previousFormShown.style.display = 'none';
		}
		if (form_wrapper) {
			form_wrapper.style.display = 'block';
			CommentsControl.previousFormShown = form_wrapper;
		}
		var parent_comment = document.getElementById(parent_comment_holder);
		if (parent_comment) {
			parent_comment.value = comment_id;
		}
	},

	answer		: function(form_wrapper_control) {
		var form_wrapper = document.getElementById(form_wrapper_control);
		if (CommentsControl.previousFormShown) {
			CommentsControl.previousFormShown.style.display = 'none';
		}
		if (form_wrapper) {
			form_wrapper.style.display = 'block';
			CommentsControl.previousFormShown = form_wrapper;
		}
	}
};
