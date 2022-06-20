M.availability_arlo = M.availability_arlo || {};

M.availability_arlo.form = Y.Object(M.core_availability.plugin);

M.availability_arlo.form.initInner = function(param) {
    // See https://docs.moodle.org/dev/Availability_conditions.
};

M.availability_arlo.form.getNode = function(json) {
    var strings = M.str.availability_arlo;
    return Y.Node.create('<span>' + strings.title + '</span>');
};

M.availability_arlo.form.fillValue = function(value, node) {
    // See https://docs.moodle.org/dev/Availability_conditions.
};

M.availability_arlo.form.fillErrors = function(errors, node) {
    // See https://docs.moodle.org/dev/Availability_conditions.
};

M.availability_arlo.form.focusAfterAdd = function(node) {
    // See https://docs.moodle.org/dev/Availability_conditions.
};