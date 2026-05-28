var script = (function () {
    function randomString() {
        var possible =
            "AbCdEfGhIjKlMnOpQrStUvWxYzaBcDeFgHiJkLmNoPqRsTuVwXyZ135792468";
        var randomString = "";
        var character, randomNumber;
        while (randomString.length < 5) {
            randomNumber = Math.floor(Math.random() * Math.floor(60));
            character = possible.substr(randomNumber, 1);
            randomString += character;
        }
        return randomString;
    }

    function calcTotal() {
        var total = 0;
        $("#calc-total .figure").each(function () {
            var curFigure = $(this).val() != "" ? $(this).val() : 0;
            total += parseFloat(curFigure);
        });
        $("#figure-total").val(total.toFixed(2));
    }

    function toggleSubmitButton() {
        if ($("#selected-employees table").length > 0) {
            $("#outcome-submit-button").removeClass("hide");
            $("#outcome-save-button").removeClass("hide");
        } else {
            $("#outcome-submit-button").addClass("hide");
            $("#outcome-save-button").addClass("hide");
        }
    }
    function initialize() {
        $("[data-toggle='tooltip']").tooltip();
        if ($("#disable-return").length > 0) {
            $(document).keydown(function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                }
            });
        }
        $("#change-pw-form").on("submit", function (e) {
            var form = $(this);
            var firstField = $("#new-pw-1").val();
            var secondField = $("#new-pw").val();
            if (firstField !== secondField) {
                e.preventDefault();
                $.alert(
                    "Please enter the same password twice for confirmation"
                );
            } else {
                return true;
            }
        });
        /*$(document).on("click",".display-pdf",function(e){
            //return true;
            e.preventDefault();
            var form = $(this).closest('form');
            var z = form.find("input[name='z']").val();
            var w = form.find("input[name='w']").val();

            $("#display-pdf-modal .modal-content").empty();
            $("#display-pdf-modal .modal-content").html("<iframe style='height:100%;' src='https://pms.tashicell.com/filedisplay?z="+z+"&w="+w+"'></iframe>");
            $("#display-pdf-modal").modal('show');
        });*/
        $(".editconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to edit this record?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".addconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to add tasks for this goal?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".viewall-confirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content:
                    "Any unsaved changes will be lost. Are you sure you want to view all saved records?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".closeconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to close this PMS Round?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".openconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to open this PMS Round?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".logout-confirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to logout?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });
        $(".deleteconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content:
                    "Are you sure you want to delete this record? This action cannot be undone.",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        $(document).on("change", "#DepartmentId", function () {
            var deptId = $(this).val();
            if (deptId !== "") {
                $("#DesignationLocation option").each(function () {
                    var curOption = $(this);
                    var deptIdOption = curOption.data("deptids");
                    var check = false;
                    for (var x in deptIdOption) {
                        if (parseInt(deptIdOption[x]) === parseInt(deptId)) {
                            check = true;
                        }
                    }
                    if (!check) {
                        curOption.addClass("hide").prop("disabled", true);
                    } else {
                        curOption.removeClass("hide").prop("disabled", false);
                    }
                });
                $("#DesignationLocation").val("");
                $("#DesignationLocation").select2("destroy");
                $("#DesignationLocation").select2({
                    theme: "bootstrap4",
                });
            } else {
                $("#DesignationLocation option")
                    .removeClass("hide")
                    .prop("disabled", false);
                $("#DesignationLocation").val("");
                $("#DesignationLocation").select2("destroy");
                $("#DesignationLocation").select2({
                    theme: "bootstrap4",
                });
            }
        });
        $(".select2:not(.select2multiple)").select2({
            theme: "bootstrap4",
        });
        $(".select2multiple").select2({
            theme: "bootstrap4",
            placeholder: "All",
        });
        $(".select2multiple2").select2({
            theme: "bootstrap4",
            placeholder: "All Departments",
        });

        if ($("#DepartmentId").length && $("#DesignationLocation").length) {
            var deptId = $("#DepartmentId").val();
            if (deptId !== "") {
                $("#DesignationLocation option[value!='']").each(function () {
                    var curOption = $(this);
                    var deptIdOption = curOption.data("deptids");
                    var check = false;
                    for (var x in deptIdOption) {
                        if (parseInt(deptIdOption[x]) === parseInt(deptId)) {
                            check = true;
                        }
                    }
                    if (!check) {
                        curOption.addClass("hide").prop("disabled", true);
                    } else {
                        curOption.removeClass("hide").prop("disabled", false);
                    }
                });
                $("#DesignationLocation").select2("destroy");
                $("#DesignationLocation").select2({
                    theme: "bootstrap4",
                });
            }
        }
        $(".reset-password").on("click", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var name = $(this).data("name");
            var baseUrl = $("body").data("baseurl");
            $.confirm({
                title: "Reset Password",
                content:
                    "" +
                    '<form action="' +
                    baseUrl +
                    '/resetpassword" class="formName" >' +
                    '<div class="form-group">' +
                    "<label>Enter new password for " +
                    name +
                    "</label>" +
                    '<input type="hidden" placeholder="Your name" value="' +
                    id +
                    '" name="Id" class="id form-control" required />' +
                    '<input type="text" placeholder="New Password" name="Password" class="password form-control" required />' +
                    "</div>" +
                    "</form>",
                buttons: {
                    formSubmit: {
                        text: "Submit",
                        btnClass: "btn-blue",
                        action: function () {
                            var password = this.$content
                                .find(".password")
                                .val();
                            var id = this.$content.find(".id").val();
                            if (!password) {
                                $.alert("Provide a Password");
                                return false;
                            } else {
                                password = encodeURIComponent(
                                    encodeURI(password)
                                );
                                window.location.href =
                                    baseUrl +
                                    "/resetpassword?Id=" +
                                    id +
                                    "&Password=" +
                                    password;
                            }
                        },
                    },
                    cancel: function () {
                        //close
                    },
                },
                onContentReady: function () {
                    // bind to events
                    var jc = this;
                    this.$content.find("form").on("submit", function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger("click"); // reference the button and click it
                    });
                },
            });
        });

        $(".fetch-error-detail").on("click", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var baseUrl = $("body").data("baseurl");
            $("#error-detail .modal-body").empty();
            $.ajax({
                url: baseUrl + "/fetcherrordetail",
                dataType: "JSON",
                type: "POST",
                data: { id: id },
                success: function (data) {
                    $("#error-detail .modal-body").html(
                        "<pre>" + data.detail + "</pre>"
                    );
                    $("#error-detail").modal("show");
                },
            });
        });
        $(".fetch-pms-history").on("click", function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var baseUrl = $("body").data("baseurl");
            $("#pms-history .modal-body").empty();
            $("#pms-history .modal-body").load(
                baseUrl + "/loadpmshistory/" + id,
                function (data) {
                    $("#pms-history").modal("show");
                }
            );
        });
        $(".setcriteria").on("click", function () {
            var deptId = $(this).data("deptid");
            var positionId = $(this).data("positionid");
            var dept = $(this).data("dept");
            var position = $(this).data("position");
            $("#position-name").html("<em>" + position + "</em>");
            $("#dept-name").text(dept + " Department");
            var baseUrl = $("body").data("baseurl");
            $("#criteria-modal-form").load(
                baseUrl + "/fetchcriteriainput",
                { deptId: deptId, positionId: positionId },
                function () {
                    $("#criteria-modal").modal("show");
                }
            );
        });
        $(document).on("click", ".add-new-row", function () {
            var curElement = $(this);
            var curTable = curElement.closest("table");
            var lastRow = curTable.find("tr").not(".dont-clone").last();
            var clonedRow = lastRow.clone();
            var curControl, curControlName, indexOfFirstBracket, oldString;
            var newString = randomString();
            clonedRow.find("input,select,textarea").each(function () {
                curControl = $(this);
                if (
                    curControl.attr("type") != "checkbox" &&
                    curControl.attr("type") != "radio"
                ) {
                    curControl.val("");
                }

                curControlName = curControl.attr("name");
                indexOfFirstBracket = curControlName.indexOf("[");
                oldString = curControlName.substr(indexOfFirstBracket + 1, 5);
                curControl.attr(
                    "name",
                    curControlName.replace(oldString, newString)
                );
                if (
                    curControl.attr("type") == "checkbox" ||
                    curControl.attr("type") == "radio"
                ) {
                    curControl.removeAttr("checked");
                    curControl.prop("checked", false);
                }
            });
            lastRow.after(clonedRow);
        });
        $(document).on("click", ".delete-row", function () {
            var curElement = $(this);
            var curTable = curElement.closest("table");
            var rowCount = curTable.find("tbody tr").not(".dont-clone").length;
            if (rowCount > 1) {
                var reply = true;
                if (curElement.hasClass("has-confirmation")) {
                    reply = confirm(curElement.data("message"));
                }
                if (reply) {
                    curElement.closest("tr").remove();
                }
            } else {
                $.alert("<strong>Sorry!</strong> You cannot delete all rows");
            }
            if (curTable.find(".goal-weightage").length) {
                console.log("here");
                var tableTotal = 0;
                curTable.find(".goal-weightage").each(function () {
                    tableTotal +=
                        $(this).val() === "" ? 0 : parseFloat($(this).val());
                });
                curTable.find(".goal-total").val(tableTotal.toFixed(1));
            }
        });
        $(document).on("submit", "#set-criteria-form", function (e) {
            var passed = true;
            var form = $(this).closest("form");
            var value1 = $("#WeightageForLevel1").val();
            var value2 =
                $("#WeightageForLevel2").val() == ""
                    ? 0
                    : $("#WeightageForLevel2").val();
            var ratingTotal = parseFloat(value1) + parseFloat(value2);
            var message = "";
            if (ratingTotal != 100.0) {
                passed = false;
                message += "<br/>The total for Rating Weightage should be 100.";
            }
            var assessmentTotal = 0;
            $(".assessment-weightage").each(function () {
                if ($(this).val() != "") {
                    assessmentTotal += parseFloat($(this).val());
                }
            });
            if (parseFloat(assessmentTotal) != 100.0) {
                passed = false;
                message +=
                    "<br/>The total for Assessment Area Weightage should be 100.";
            }
            if (!passed) {
                e.preventDefault();
                $.alert(
                    message +
                        "<br/><br/><strong>Please correct these errors in order to set criteria</strong>"
                );
            } else {
                $("#validate-criteria").attr("disabled", "disabled");
                return true;
            }
        });

        $(document).on("submit", "#set-goals-target-form", function (e) {
            var passed = true;
            var form = $(this).closest("form");
            var value1 = $("#WeightageForLevel1").val();
            var value2 =
                $("#WeightageForLevel2").val() == ""
                    ? 0
                    : $("#WeightageForLevel2").val();
            var ratingTotal = parseFloat(value1) + parseFloat(value2);
            var message = "";
            if (ratingTotal != 100.0) {
                passed = false;
                message += "<br/>The total for Rating Weightage should be 100.";
            }
            var assessmentTotal = 0;
            $(".assessment-weightage").each(function () {
                if ($(this).val() != "") {
                    assessmentTotal += parseFloat($(this).val());
                }
            });
            if (parseFloat(assessmentTotal) != 100.0) {
                passed = false;
                message +=
                    "<br/>The total for Assessment Area Weightage should be 100.";
            }
            if (!passed) {
                e.preventDefault();
                $.alert(
                    message +
                        "<br/><br/><strong>Please correct these errors in order to set criteria</strong>"
                );
            } else {
                $("#validate-criteria").attr("disabled", "disabled");
                return true;
            }
        });

        $(".approveconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to approve this record?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        $(".resendconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to resend this record?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        $(".resubmitconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to resubmit this record?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        $(".submitconfirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to resubmit this record?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // fetch my goals and targets for tasks
        $(".goal-type-id").on("change", function () {
            var goalTypeId = $(".goal-type-id").val();
            var employeeId = $("#EmpId").val();
            if (goalTypeId != "" && employeeId != "") {
                var baseUrl = $("body").data("baseurl");
                $.ajax({
                    url:
                        baseUrl +
                        "/fetchmygoalstargetsfortask/" +
                        goalTypeId +
                        "/" +
                        employeeId,
                    type: "GET",
                    dataType: "JSON",
                    success: function (data) {
                        $("select#GoalId").empty();
                        var html = "<option value=''>--SELECT ONE--</option>";
                        for (var x in data) {
                            html +=
                                "<option value='" +
                                data[x].Id +
                                "'>" +
                                data[x].GoalDescription +
                                "</option>";
                        }
                        $("select#GoalId").html(html);
                        $("#GoalId").select2("destroy");
                        $("#GoalId").select2({
                            theme: "bootstrap4",
                        });
                    },
                });
            } else {
                location.reload();
                $("select#GoalId").empty();
                var html = "<option value=''>--SELECT ONE--</option>";
                $("select#GoalId").html(html);
                $("#GoalId").select2("destroy");
                $("#GoalId").select2({
                    theme: "bootstrap4",
                });
            }
        });

        // fetch goals and targets details for tasks
        $(".goal-type-id").on("change", function () {
            var goalTypeId = $(".goal-type-id").val();
            var employeeId = $("#EmpId").val();
            if (goalTypeId != "" && employeeId != "") {
                $(".goal-id").on("change", function () {
                    var goalId = $(".goal-id").val();
                    if (goalId != "") {
                        var baseUrl = $("body").data("baseurl");
                        $.ajax({
                            url:
                                baseUrl +
                                "/fetchgoaldetailsfortasks/" +
                                goalTypeId +
                                "/" +
                                goalId +
                                "/" +
                                employeeId,
                            type: "GET",
                            dataType: "JSON",
                            success: function (data) {
                                var goalWeightage = data[0].Weightage;
                                var goalTarget = data[0].Target;
                                $("#GoalWeightage").val(goalWeightage);
                                $("#GoalTarget").val(goalTarget);
                            },
                        });
                    } else {
                        location.reload();
                    }
                });
            } else {
                location.reload();
            }
        });

        // fetch department goals and targets for tasks
        $(".goaltypeid").on("change", function () {
            var goalTypeId = $(".goaltypeid").val();
            var departmentId = $("#DepartmentId").val();
            var sectionId = $("#SectionId").val();

            if (goalTypeId != "" && departmentId != "" && sectionId != "") {
                var baseUrl = $("body").data("baseurl");
                $.ajax({
                    url:
                        baseUrl +
                        "/fetchdepartmentgoalstargetsfortask/" +
                        goalTypeId +
                        "/" +
                        departmentId +
                        "/" +
                        sectionId,
                    type: "GET",
                    dataType: "JSON",
                    success: function (data) {
                        $("select#GoalId").empty();
                        var html = "<option value=''>--SELECT ONE--</option>";
                        for (var x in data) {
                            html +=
                                "<option value='" +
                                data[x].Id +
                                "'>" +
                                data[x].GoalDescription +
                                "</option>";
                        }
                        $("select#GoalId").html(html);
                        $("#GoalId").select2("destroy");
                        $("#GoalId").select2({
                            theme: "bootstrap4",
                        });
                    },
                });
            } else {
                location.reload();
                $("select#GoalId").empty();
                var html = "<option value=''>--SELECT ONE--</option>";
                $("select#GoalId").html(html);
                $("#GoalId").select2("destroy");
                $("#GoalId").select2({
                    theme: "bootstrap4",
                });
            }
        });

        $(".goaltypeid").on("change", function () {
            var goalTypeId = $(".goaltypeid").val();
            if (goalTypeId != "") {
                $(".goal-id").on("change", function () {
                    var goalId = $(".goal-id").val();
                    if (goalId != "") {
                        var baseUrl = $("body").data("baseurl");
                        $.ajax({
                            url:
                                baseUrl +
                                "/fetchgoalstargetsdetailsfortask/" +
                                goalTypeId +
                                "/" +
                                goalId,
                            type: "GET",
                            dataType: "JSON",
                            success: function (data) {
                                var employeeList = [];
                                var goalTargetList = [];
                                var goalWeightageList = [];

                                for (let i = 0; i < data.length; i++) {
                                    var employeeName = data[i].EmployeeName;
                                    var goalTarget = data[i].Target;
                                    var goalWeightage = data[i].Weightage;

                                    if (!employeeList.includes(employeeName)) {
                                        employeeList.push(employeeName);
                                    }
                                    if (!goalTargetList.includes(goalTarget)) {
                                        goalTargetList.push(goalTarget);
                                    }
                                    if (
                                        !goalWeightageList.includes(
                                            goalWeightage
                                        )
                                    ) {
                                        goalWeightageList.push(goalWeightage);
                                    }

                                    var employeeIds = employeeList.join(", ");
                                    var targets = goalTargetList.join(", ");
                                    var weightages =
                                        goalWeightageList.join(", ");

                                    $("#GoalAssignedSubOrdinatesId").val(
                                        employeeIds
                                    );
                                    $("#GoalTarget").val(targets);
                                    $("#GoalWeightage").val(weightages);
                                }
                            },
                        });
                    } else {
                        location.reload();
                    }
                });
            } else {
                location.reload();
            }
        });

        // task weighage validation using goal id
        var employeeId = $("#AssignEmployeeId").val();
        var goalId = $("#GoalId").val();
        var goalWeightage = Number($("#GoalWeightage").val());
        if (goalId != "" && goalWeightage != "") {
            $("#TaskWeightage").val(goalWeightage);
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url:
                    baseUrl + "/fetchtasksdetails/" + goalId + "/" + employeeId,
                type: "GET",
                dataType: "JSON",
                success: function (data) {
                    let taskWeightage = 0;
                    let taskTotalWeightage = 0;
                    for ($i = 0; $i < data.length; $i++) {
                        taskWeightage = Number(data[$i].Weightage);
                        taskTotalWeightage = taskTotalWeightage + taskWeightage;
                    }

                    let taskLeftOverWeightage = 0;
                    taskLeftOverWeightage = goalWeightage - taskTotalWeightage;
                    $("#TaskWeightage").val(taskLeftOverWeightage);
                    if (taskLeftOverWeightage <= 0) {
                        $.alert(
                            (message =
                                "<strong>You cannot add more tasks since the goal weightage is fully used.</strong>")
                        );
                    }
                },
            });
        }

        // fetch goals and tasks weightage validation
        $("#Weightage").on("change", function () {
            var passed = true;
            var taskWeightage = Number($("#Weightage").val());
            var taskLeftOverWeightage = Number($("#TaskWeightage").val());
            if (taskWeightage > taskLeftOverWeightage) {
                passed = false;
                $.alert(
                    (message =
                        "<strong>Task Weightage is more than Goal Weightage !</strong>")
                );
                $("#Weightage").val("");
            } else if (taskWeightage <= 0) {
                passed = false;
                $.alert(
                    (message =
                        "<strong>Task Weightage should be more than 0 !</strong>")
                );
                $("#Weightage").val("");
            } else {
                passed = true;
            }
        });

        // fetch employees under same department
        var departmentId = $(".fetch-employees-by-department-id").val();
        if (departmentId != "") {
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url: baseUrl + "/fetchemployeesbydepartmentId/" + departmentId,
                type: "GET",
                dataType: "JSON",
                success: function (data) {
                    $("select#EmployeeId").empty();
                    var html = "<option value=''>--SELECT ONE--</option>";
                    for (var x in data) {
                        html +=
                            "<option value='" +
                            data[x].EmpId +
                            "'>" +
                            data[x].EmployeeName +
                            " (" +
                            data[x].EmpId +
                            "), (" +
                            data[x].Designation +
                            ")</option>";
                    }
                    $("select#EmployeeId").html(html);
                    $("#EmployeeId").select2("destroy");
                    $("#EmployeeId").select2({
                        theme: "bootstrap4",
                    });
                },
            });
        } else {
            location.reload();
        }

        // fetch employeess by userId, departmentId and sectionId
        $(".fetch-employees-id").on("change", function () {
            var userId = $("#UserId").val();
            var departmentId = $("#DepartmentId").val();
            var sectionId = $(".fetch-employees-id").val();
            if (userId != "" && departmentId != "" && sectionId != "") {
                var baseUrl = $("body").data("baseurl");
                $.ajax({
                    url:
                        baseUrl +
                        "/fetchemployeesundersupervisor/" +
                        userId +
                        "/" +
                        departmentId +
                        "/" +
                        sectionId,
                    type: "GET",
                    dataType: "JSON",
                    success: function (data) {
                        $("select#EmployeeId").empty();
                        var html = "<option value=''>--SELECT ONE--</option>";
                        for (var x in data) {
                            html +=
                                "<option value='" +
                                data[x].EmpId +
                                "'>" +
                                data[x].EmployeeName +
                                " (" +
                                data[x].EmpId +
                                "), (" +
                                data[x].Designation +
                                ")</option>";
                        }
                        $("select#EmployeeId").html(html);
                        $("#EmployeeId").select2("destroy");
                        $("#EmployeeId").select2({
                            theme: "bootstrap4",
                        });
                    },
                });
            } else {
                // location.reload();
            }
        });

        // auto calculate score for supervisor and subordinate using goal achievement
        $(".goal-achievement-id").on("change", function () {
            var achievementId = $(".goal-achievement-id").val();
            var goalId = $("#GoalId").val();
            if (achievementId != "" && goalId != "") {
                var baseUrl = $("body").data("baseurl");
                $.ajax({
                    url:
                        baseUrl + "/fetchgoalweightageforachievement/" + goalId,
                    type: "GET",
                    dataType: "JSON",
                    success: function (data) {
                        var goalWeightage = Number(data[0].Weightage);
                        if (achievementId == 1) {
                            $("#SelfScore").val(goalWeightage);
                            $("#SupervisorScore").val(goalWeightage);
                        } else if (achievementId == 2) {
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (achievementId == 3) {
                            $("#SelfScore").val(0);
                            $("#SupervisorScore").val(0);
                        }
                    },
                });
            } else {
                // location.reload();
            }
        });

        // goal score validation for supervisor and subordinate
        $(".goal-score-validate").on("change", function () {
            var message = "";
            var goalId = $("#GoalId").val();
            var goalScore = Number($(".goal-score-validate").val());
            var achievementId = $(".goal-achievement-id").val();
            var goalWeightage = 0;
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url: baseUrl + "/fetchgoalweightageforachievement/" + goalId,
                type: "GET",
                dataType: "JSON",
                success: function (data) {
                    goalWeightage = Number(data[0].Weightage);
                    if (achievementId == 1) {
                        // Fully Achieved
                        if (goalScore > goalWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be more than Goal Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (goalScore < goalWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be less than Goal Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        }
                    } else if (achievementId == 2) {
                        // Partially Achieved
                        if (goalScore >= goalWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be more than or equal to Goal Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (goalScore <= 0) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be less than or equal to 0 !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        }
                    } else if (achievementId == 3) {
                        // Not Achieved
                        if (goalScore >= goalWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be more than Goal Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (goalScore < goalWeightage) {
                            if (goalScore > 0) {
                                $.alert(
                                    (message =
                                        "<strong>Your Score should not be more than 0 !</strong>")
                                );
                                $("#SelfScore").val("");
                                $("#SupervisorScore").val("");
                            } else if (goalScore < 0) {
                                $.alert(
                                    (message =
                                        "<strong>Your Score should not be less than 0 !</strong>")
                                );
                                $("#SelfScore").val("");
                                $("#SupervisorScore").val("");
                            }
                        }
                    }
                },
            });
        });

        // auto calculate score for supervisor and subordinate using task goal achievement
        $(".task-achievement-id").on("change", function () {
            var achievementId = $(".task-achievement-id").val();
            var taskId = $("#TaskId").val();
            if (achievementId != "" && taskId != "") {
                var baseUrl = $("body").data("baseurl");
                $.ajax({
                    url:
                        baseUrl + "/fetchtaskweightageforachievement/" + taskId,
                    type: "GET",
                    dataType: "JSON",
                    success: function (data) {
                        var taskWeightage = Number(data[0].Weightage);
                        if (achievementId == 1) {
                            $("#SelfScore").val(taskWeightage);
                            $("#SupervisorScore").val(taskWeightage);
                        } else if (achievementId == 2) {
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (achievementId == 3) {
                            $("#SelfScore").val(0);
                            $("#SupervisorScore").val(0);
                        }
                    },
                });
            } else {
                location.reload();
            }
        });

        // task score validation for supervisor and subordinate
        $(".task-score-validate").on("change", function () {
            var message = "";
            var taskId = $("#TaskId").val();
            var taskScore = Number($(".task-score-validate").val());
            var achievementId = $(".task-achievement-id").val();
            var taskWeightage = 0;
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url: baseUrl + "/fetchtaskweightageforachievement/" + taskId,
                type: "GET",
                dataType: "JSON",
                success: function (data) {
                    taskWeightage = Number(data[0].Weightage);
                    if (achievementId == 1) {
                        // Fully Achieved
                        if (taskScore > taskWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be more than Task Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (taskScore < taskWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be less than Task Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        }
                    } else if (achievementId == 2) {
                        // Partially Achieved
                        if (taskScore >= taskWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be more than or equal to Task Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (taskScore <= 0) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be less than or equal to 0 !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        }
                    } else if (achievementId == 3) {
                        // Not Achieved
                        if (taskScore >= taskWeightage) {
                            $.alert(
                                (message =
                                    "<strong>Your Score should not be more than Task Weightage !</strong>")
                            );
                            $("#SelfScore").val("");
                            $("#SupervisorScore").val("");
                        } else if (taskScore < taskWeightage) {
                            if (taskScore > 0) {
                                $.alert(
                                    (message =
                                        "<strong>Your Score should not be more than 0 !</strong>")
                                );
                                $("#SelfScore").val("");
                                $("#SupervisorScore").val("");
                            } else if (taskScore < 0) {
                                $.alert(
                                    (message =
                                        "<strong>Your Score should not be less than 0 !</strong>")
                                );
                                $("#SelfScore").val("");
                                $("#SupervisorScore").val("");
                            }
                        }
                    }
                },
            });
        });

        // disable target month and showing target description
        $(".targetId").hide().find("input").removeAttr("required");
        $(".custom-target-enable").hide();
        $(".show-employee-details").hide();
        $(".goal-target").on("change", function () {
            var goalTarget = $(".goal-target").val();
            if (goalTarget == "Custom Targets") {
                $(".custom-target-enable")
                    .show()
                    .find("input")
                    .removeAttr("required");
                $("#TargetDescription").on("change", function () {
                    var targetDescription = $("#TargetDescription").val();
                    $("#Target").val(targetDescription);
                });
            } else {
                $("#Target").val(goalTarget);
                $(".custom-target-enable")
                    .hide()
                    .find("input")
                    .removeAttr("required");
            }
        });

        // fetch employees under supervisor
        $(".fetch-section-id").on("change", function () {
            var userId = $("#UserId").val();
            var departmentId = $("#DepartmentId").val();
            if (userId != "" && departmentId != "") {
                var sectionId = $(".fetch-section-id").val();
                if (sectionId != "") {
                    var baseUrl = $("body").data("baseurl");
                    $.ajax({
                        url:
                            baseUrl +
                            "/fetchemployeesundersubordinate/" +
                            userId +
                            "/" +
                            departmentId +
                            "/" +
                            sectionId,
                        type: "GET",
                        dataType: "JSON",
                        success: function (data) {
                            $("select#AssignEmployeeId").empty();
                            var html =
                                "<option value=''>--SELECT ONE--</option>";
                            for (var x in data) {
                                html +=
                                    "<option value='" +
                                    data[x].EmpId +
                                    "'>" +
                                    data[x].EmployeeName +
                                    " (" +
                                    data[x].EmpId +
                                    "), (" +
                                    data[x].Designation +
                                    ")</option>";
                            }
                            $("select#AssignEmployeeId").html(html);
                            $("#AssignEmployeeId").select2("destroy");
                            $("#AssignEmployeeId").select2({
                                theme: "bootstrap4",
                            });
                        },
                    });
                } else {
                    location.reload();
                }
            } else {
                location.reload();
            }
        });

        // $("#ExcelApplicant").on('change',function(){
        //     if(!$("#ExcelApplicant").val()){
        //         $.alert("Please select a file to upload!");
        //     }else{
        //         $("#score").val('');
        //         var formData = new FormData();
        //         formData.append('file',$("#ExcelApplicant")[0].files[0]);
        //         var baseUrl = $("body").data('baseurl');
        //
        //         $.ajax({
        //             url: baseUrl+"/uploadexcelapplicant",
        //             type: "POST",
        //             contentType: false,
        //             processData: false,
        //             data: formData,
        //             success: function (data) {
        //
        //                 if(data.success == false){
        //                     $.alert(data.message);
        //                 }else{
        // var finalScore = data.score;
        // var max = $("#score").attr('max');
        // var min = $("#score").attr('min');
        // var description = $("#score").closest('tr').find('.description').text();
        // if(parseFloat(finalScore) > parseFloat(max)){
        //     $.alert(description+" cannot have more than "+max+" points. Please enter correct value.");
        // }else if(parseFloat(finalScore) < parseFloat(min)){
        //     $.alert(description+" cannot have less than "+min+" points. Please enter correct value.");
        // }else{
        //     $("#score").val(finalScore);
        // }
        // calcTotal();
        //                 }
        //             }
        //         });
        //     }
        // });
        // $("#ExcelApplicant2").on('change',function(){
        //     if(!$("#ExcelApplicant2").val()){
        //         $.alert("Please select a file to upload!");
        //     }else{
        //         $("#score").val('');
        //         var formData = new FormData();
        //         formData.append('file',$("#ExcelApplicant2")[0].files[0]);
        //         var baseUrl = $("body").data('baseurl');
        //
        //         $.ajax({
        //             url: baseUrl+"/uploadexcelapplicant2",
        //             type: "POST",
        //             contentType: false,
        //             processData: false,
        //             data: formData,
        //             success: function (data) {
        //                 if(data.success == false){
        //                     $.alert(data.message);
        //                 }
        //             }
        //         });
        //     }
        // });
        $(document).on("keyup change click", ".figure", function () {
            var value = $(this).val();
            var max = $(this).attr("max");
            var min = $(this).attr("min");
            var description = $(this).closest("tr").find(".description").text();
            if (parseFloat(value) > parseFloat(max)) {
                $(this).val("");
                $.alert(
                    description +
                        " cannot have more than " +
                        max +
                        " points. Please enter correct value."
                );
            } else if (parseFloat(value) < parseFloat(min)) {
                $(this).val("");
                $.alert(
                    description +
                        " cannot have less than " +
                        min +
                        " points. Please enter correct value."
                );
            }
            calcTotal();
        });
        $(document)
            .ajaxStart(function () {
                $("#loader").removeClass("hide");
            })
            .ajaxStop(function () {
                $("#loader").addClass("hide");
            });
        $(document).on("click", "#send-back", function (e) {
            e.preventDefault();
            var linkHref = $(this).attr("href");
            var remarks = $("#Remarks").val();
            if (remarks == "") {
                $.alert(
                    "Please enter a remarks when Sending back an Application"
                );
            } else {
                window.location.href = linkHref + "?remarks=" + remarks;
            }
        });
        $(document).on("submit", "form", function () {
            $(this)
                .find("button[type='submit']")
                .not("#validate-criteria")
                .not(".dont-disable")
                .attr("disabled", "disabled");
        });
        $(document).on("keyup keydown change", ".goal-weightage", function () {
            var table = $(this).closest("table");
            var tableTotal = 0;
            table.find(".goal-weightage").each(function () {
                tableTotal +=
                    $(this).val() === "" ? 0 : parseFloat($(this).val());
            });
            table.find(".goal-total").val(tableTotal.toFixed(1));
        });
        $(".save-goals").on("click", function (e) {
            var status = $(this).data("status");
            var form = document.getElementById("goals-form");
            $("#goal-status").val(status);
            if (form.checkValidity()) {
                var tableTotal = 0;
                $(".goal-weightage").each(function () {
                    tableTotal +=
                        $(this).val() === "" ? 0 : parseFloat($(this).val());
                });
                if (parseFloat(tableTotal) !== 100.0) {
                    e.preventDefault();
                    $.alert("Total must equal 100 marks");
                }
            }
        });
        $("#subordinate-performance-goal").on("click", function (e) {
            var id = $("input[name='Id']").val();
            console.log(id);
            $("#subordinate-pg-form").load(
                "/fetchsubordinategoals",
                { id: id },
                function (data) {
                    $("#subordinate-pg-modal").modal("show");
                }
            );
        });
        $("#subordinate-performance-goal-l2").on("click", function (e) {
            var id = $("input[name='Id']").val();
            console.log(id);
            $("#subordinate-pg-form").load(
                "/fetchsubordinategoalsl2",
                { id: id },
                function (data) {
                    $("#subordinate-pg-modal").modal("show");
                }
            );
        });
        $(document).on("click", "#save-appraisee", function () {
            var form = $(this).closest("form");
            var inputs = new FormData(form[0]);
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url: baseUrl + "/saveappraisee",
                type: "POST",
                data: inputs,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.success == true) {
                        window.location.reload();
                    } else {
                        $.alert(data.message);
                    }
                },
            });
        });
        $(document).on("click", "#save-appraiser", function () {
            var form = $(this).closest("form");
            var inputs = new FormData(form[0]);
            var baseUrl = $("body").data("baseurl");
            $.ajax({
                url: baseUrl + "/saveappraiser",
                type: "POST",
                data: inputs,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.success == true) {
                        window.location.reload();
                    } else {
                        $.alert(data.message);
                    }
                },
            });
        });

        $(document).on("change", "#fetch-employees-dept", function () {
            var deptId = $(this).val();
            var baseUrl = $("body").data("baseurl");
            if (deptId != "") {
                $.ajax({
                    url:
                        baseUrl +
                        "/fetchdepartmentemployees/" +
                        deptId +
                        "/" +
                        0 +
                        "/" +
                        1,
                    type: "GET",
                    dataType: "JSON",
                    success: function (data) {
                        $("select#fetched-employees").empty();
                        var html = "<option value=''>--SELECT ONE--</option>";
                        for (var x in data) {
                            html +=
                                "<option value='" +
                                data[x].Id +
                                "'>" +
                                data[x].Name +
                                " - CID No:" +
                                data[x].CIDNo +
                                ", Emp Id:" +
                                data[x].EmpId +
                                " (" +
                                data[x].Designation +
                                ")</option>";
                        }
                        $("select#fetched-employees").html(html);
                        $("#fetched-employees").select2("destroy");
                        $("#fetched-employees").select2({
                            theme: "bootstrap4",
                        });
                    },
                });
            } else {
            }
        });

        $(document).on("change", ".populate-basic-pay", function () {
            if ($(this).val() != "") {
                var basicPay = $("option:selected", this).data("basicpay");
                if (basicPay > 0) $("#BasicPay").val(basicPay);
                else $("#BasicPay").val("");
            } else {
                $("#BasicPay").val("");
            }
        });

        $(document).on("change", "#filter-section", function () {
            var deptId = $(this).val();
            if (deptId != "") {
                $("#select-department option[value!='']").each(function () {
                    var curOption = $(this);
                    var optionDeptId = curOption.data("departmentid");
                    if (optionDeptId == deptId) {
                        $(this).removeClass("hide").prop("disabled", false);
                    } else {
                        $(this).addClass("hide").prop("disabled", true);
                    }
                });
                if ($(this).hasClass("fetch-employee-on-dept")) {
                    var baseUrl = $("body").data("baseurl");
                    $.ajax({
                        url:
                            baseUrl +
                            "/fetchdepartmentemployees/" +
                            deptId +
                            "/" +
                            0 +
                            "/" +
                            1,
                        type: "GET",
                        dataType: "JSON",
                        success: function (data) {
                            $("select#fetched-employees").empty();
                            var html = "<option value=''>All</option>";
                            for (var x in data) {
                                html +=
                                    "<option value='" +
                                    data[x].Id +
                                    "'>" +
                                    data[x].Name +
                                    " (" +
                                    data[x].Designation +
                                    ")</option>";
                            }
                            $("select#fetched-employees").html(html);
                            $("#fetched-employees").select2("destroy");
                            $("#fetched-employees").select2({
                                theme: "bootstrap4",
                            });
                        },
                    });
                }
            } else {
                $("select#fetched-employees").empty();
                var html = "<option value=''>All</option>";
                $("select#fetched-employees").html(html);
                $("#fetched-employees").select2("destroy");
                $("#fetched-employees").select2({
                    theme: "bootstrap4",
                });

                $("#select-department option")
                    .removeClass("hide")
                    .prop("disabled", false);
            }
            $("#select-department option").prop("selected", false);
            $("#select-department").select2("destroy");
            $("#select-department").select2({
                theme: "bootstrap4",
            });
        });

        $(document).on("change", "#select-department", function () {
            var sectionId = $(this).val();
            if (sectionId != "") {
                var deptId = $("option:selected", this).data("departmentid");
                $("#filter-section option[value='" + deptId + "']").prop(
                    "selected",
                    true
                );
                $("#filter-section").select2("destroy");
                $("#filter-section").select2({
                    theme: "bootstrap4",
                });

                if ($(this).hasClass("fetch-employee-on-section")) {
                    var baseUrl = $("body").data("baseurl");
                    $.ajax({
                        url:
                            baseUrl +
                            "/fetchsectionemployees/" +
                            sectionId +
                            "/" +
                            0 +
                            "/" +
                            1,
                        type: "GET",
                        dataType: "JSON",
                        success: function (data) {
                            $("select#fetched-employees").empty();
                            var html = "<option value=''>All</option>";
                            for (var x in data) {
                                html +=
                                    "<option value='" +
                                    data[x].Id +
                                    "'>" +
                                    data[x].Name +
                                    " (" +
                                    data[x].Designation +
                                    ")</option>";
                            }
                            $("select#fetched-employees").html(html);
                            $("#fetched-employees").select2("destroy");
                            $("#fetched-employees").select2({
                                theme: "bootstrap4",
                            });
                        },
                    });
                }
            } else {
                $("select#fetched-employees").empty();
                var html = "<option value=''>All</option>";
                $("select#fetched-employees").html(html);
                $("#fetched-employees").select2("destroy");
                $("#fetched-employees").select2({
                    theme: "bootstrap4",
                });

                $("#filter-section option").prop("selected", false);
                $("#filter-section").select2("destroy");
                $("#filter-section").select2({
                    theme: "bootstrap4",
                });
            }
        });
        $("#FilterType").on("change", function () {
            var value = $(this).val();
            $(".toggle-filter[data-filtertype='" + value + "']").removeClass(
                "hide"
            );
            $(
                ".toggle-filter[data-filtertype='" + value + "'] select"
            ).removeAttr("disabled");
            $(".toggle-filter[data-filtertype!='" + value + "']").addClass(
                "hide"
            );
            $(".toggle-filter[data-filtertype!='" + value + "'] select").attr(
                "disabled",
                "disabled"
            );
        });

        $("#TargetRevenue,#AchievedRevenue,#FinalAdjustmentPercent").on(
            "keyup change",
            function () {
                var targetRevenue = $("#TargetRevenue").val();
                var achievedRevenue = $("#AchievedRevenue").val();
                var finalAdjustment = $("#FinalAdjustmentPercent").val();
                if (
                    targetRevenue != "" &&
                    achievedRevenue != "" &&
                    finalAdjustment != ""
                ) {
                    var calculated =
                        (parseFloat(achievedRevenue) /
                            parseFloat(targetRevenue)) *
                        parseFloat(finalAdjustment);
                    calculated =
                        calculated > finalAdjustment
                            ? finalAdjustment
                            : calculated;
                    calculated = parseFloat(calculated);
                    $("#calculated").val(calculated.toFixed(2));
                } else {
                    $("#calculated").val("");
                }
            }
        );

	// calculation of Out of Final Adjustment
        $("#TargetRevenue, #AchievedRevenue").on("keyup change", function () {
            var targetRevenue = $("#TargetRevenue").val();
            var achievedRevenue = $("#AchievedRevenue").val();
            let finalAdjustment = 0;
            if (targetRevenue != "" && achievedRevenue != "") {
                var finalAdjustmentPercent = (parseFloat(achievedRevenue) / parseFloat(targetRevenue)) * 100;

                if (finalAdjustmentPercent >= 100) {
                    finalAdjustment = 10;
                } else if (finalAdjustmentPercent >= 98 && finalAdjustmentPercent <= 99.99) {
                    finalAdjustment = 9;
                } else if (finalAdjustmentPercent >= 96 && finalAdjustmentPercent <= 97.99) {
                    finalAdjustment = 8;
                } else if (finalAdjustmentPercent >= 93 && finalAdjustmentPercent <= 95.99) {
                    finalAdjustment = 7;
                } else if (finalAdjustmentPercent >= 90 && finalAdjustmentPercent <= 92.99) {
                    finalAdjustment = 6;
                } else if (finalAdjustmentPercent <= 89.99) {
                    finalAdjustment = 5;
                }

                $("#FinalAdjustmentPercent").val(finalAdjustment);
            } else {
                $("#FinalAdjustmentPercent").val("");
            }
        });

        $(document).on("change", "#PMSOutComeId", function () {
            var outcomeId = $(this).val();
            var reference = $("option:selected", this).data("reference");
            if (outcomeId != "") {
                $(
                    "#PayScaleWrapper input,#PayScaleWrapper select,#PositionWrapper input,#PositionWrapper select,#DesignationLocationWrapper input, #DesignationLocationWrapper select,#BasicPayWrapper input,#BasicPayWrapper select"
                ).val("");

                $(
                    "#PayScaleWrapper .select2, #BasicPayWrapper .select2, #PositionWrapper .select2, #DesignationLocationWrapper .select2"
                ).select2({
                    theme: "bootstrap4",
                });
                var payChange = $("option:selected", this).data("paychange");
                var positionChange = $("option:selected", this).data(
                    "positionchange"
                );
                var basicPayChange = $("option:selected", this).data(
                    "basicpaychange"
                );
                var designationLocationChange = $("option:selected", this).data(
                    "designationlocationchange"
                );

                if (payChange == 1) {
                    $("#PayScaleWrapper").removeClass("hide");
                } else {
                    $("#PayScaleWrapper").addClass("hide");
                }

                if (positionChange == 1) {
                    $("#PositionWrapper").removeClass("hide");
                } else {
                    $("#PositionWrapper").addClass("hide");
                }

                if (basicPayChange == 1) {
                    $("#BasicPayWrapper").removeClass("hide");
                } else {
                    $("#BasicPayWrapper").addClass("hide");
                }

                if (designationLocationChange == 1) {
                    $("#DesignationLocationWrapper").removeClass("hide");
                } else {
                    $("#DesignationLocationWrapper").addClass("hide");
                }

                if (reference == 1 || reference == 2 || reference == 5) {
                    var oldBasicPay = $("#old-basic-pay").val();
                    var increment = $("#old-payscale-increment").val();
                    if (reference == 1 || reference == 5) {
                         var newBasicPay = parseFloat(oldBasicPay) + parseFloat(increment);
                    } else {
                        var newBasicPay = parseFloat(oldBasicPay) + 2 * parseFloat(increment);
                    }

               	    $("#BasicPay").val(newBasicPay.toFixed(0));
                }
            } else {
                $("#PayScaleWrapper").addClass("hide");
                $("#PositionWrapper").addClass("hide");
                $("#BasicPayWrapper").addClass("hide");
                $("#DesignationLocationWrapper").addClass("hide");
            }
        });

        // pms outcome basic pay calculation
        var outcomeId = Number($("#PMSOutComeId").val());

        // SI, DI
        var basicpayscale = $("#BasicPayScale").val() || '';
        var oldBasicPay = Number($("#OldBasicPay").val());

        var payScaleSplit = basicpayscale.split("-");
        var firstPayScale = Number(payScaleSplit[0]);
        var increment = Number(payScaleSplit[1]);
        var lastPayScale = Number(payScaleSplit[2]);

        if (outcomeId == 2) {
            var newBasicPay = 0;
            newBasicPay = oldBasicPay + increment;
            $("#BasicPay").val(newBasicPay.toFixed(0));
        } else if (outcomeId == 3) {
            var newBasicPay = 0;
            newBasicPay = oldBasicPay + increment * 2;
            $("#BasicPay").val(newBasicPay.toFixed(0));
        }

        // grade change
        $("#Grade").on("change", function () {
            var changeInGrade = $("#Grade").val();

            if (changeInGrade != "") {
                if (
                    outcomeId == 4 ||
                    outcomeId == 5 ||
                    outcomeId == 14 ||
                    outcomeId == 15 ||
                    outcomeId == 16 ||
                    outcomeId == 17
                ) {
                    // MSP, MDP, PP, PP + SI, PP+DI, RSP
                    var oldBasicPay = Number($("#OldBasicPay").val());
                    var newBasicPay = 0;

                    var baseUrl = $("body").data("baseurl");
                    $.ajax({
                        url: baseUrl + "/getgradestep/" + changeInGrade,
                        type: "GET",
                        dataType: "JSON",
                        success: function (data) {
                            basicpayscale = data[0].PayScale || '';
                            $("#BasicPayScale").val(basicpayscale);

                            var payScaleSplit = basicpayscale.split("-");
                            var firstPayScale = Number(payScaleSplit[0]);
                            var increment = Number(payScaleSplit[1]);
                            var lastPayScale = Number(payScaleSplit[2]);

                            if (firstPayScale - oldBasicPay < increment) {
                                newBasicPay = firstPayScale + increment;
                                while (
                                    newBasicPay - oldBasicPay < increment &&
                                    newBasicPay + increment <= lastPayScale
                                ) {
                                    newBasicPay = newBasicPay + increment;
                                }
                            } else {
                                var newBasicPay = firstPayScale;
                            }

                            $("#BasicPay").val(newBasicPay.toFixed(0));
                        },
                    });
                }
            }
        });

        // change in pms outcome
        var oldBasicPayScale = $("#BasicPayScale").val();

        $("#PMSOutComeId").on("change", function () {
            var oldBasicPay = Number($("#OldBasicPay").val());
            var outcomeId = Number($("#PMSOutComeId").val());

            // SI, DI
            var newBasicPay = 0;
            var basicpayscale = $("#BasicPayScale").val() || '';

            var payScaleSplit = basicpayscale.split("-");
            var firstPayScale = Number(payScaleSplit[0]);
            var increment = Number(payScaleSplit[1]);
            var lastPayScale = Number(payScaleSplit[2]);

            if (outcomeId == 2) {
                newBasicPay = oldBasicPay + increment;
            } else if (outcomeId == 3) {
                newBasicPay = oldBasicPay + increment * 2;
            }

            $("#BasicPay").val(newBasicPay.toFixed(0));

            $("#BasicPayScale").val(oldBasicPayScale);

            $("#Grade").on("change", function () {
                var changeInGrade = $("#Grade").val();

                if (changeInGrade != "") {
                    if (
                        outcomeId == 4 ||
                        outcomeId == 5 ||
                        outcomeId == 14 ||
                        outcomeId == 15 ||
                        outcomeId == 16 ||
                        outcomeId == 17
                    ) {
                        var newBasicPay = 0;
                        // MSP, MDP, PP, PP + SI, PP+DI, RSP
                        var baseUrl = $("body").data("baseurl");
                        $.ajax({
                            url: baseUrl + "/getgradestep/" + changeInGrade,
                            type: "GET",
                            dataType: "JSON",
                            success: function (data) {
                                basicpayscale = data[0].PayScale || '';
                                $("#BasicPayScale").val(basicpayscale);

                                var payScaleSplit = basicpayscale.split("-");
                                var firstPayScale = Number(payScaleSplit[0]);
                                var increment = Number(payScaleSplit[1]);
                                var lastPayScale = Number(payScaleSplit[2]);

                                if (firstPayScale - oldBasicPay < increment) {
                                    newBasicPay = firstPayScale + increment;
                                    while (
                                        newBasicPay - oldBasicPay < increment &&
                                        newBasicPay + increment <= lastPayScale
                                    ) {
                                        newBasicPay = newBasicPay + increment;
                                    }
                                } else {
                                    newBasicPay = firstPayScale;
                                }

                                $("#BasicPay").val(newBasicPay.toFixed(0));
                            },
                        });
                    }
                }
            });
        });

        //$("#Grade").on("change", function () {
            //var basicPay = $("option:selected", this).data("basepay");
            //var lastpay = $("option:selected", this).data("lastpay");
            //basicPay = parseFloat(basicPay);
            //var increment = $("option:selected", this).data("increment");
            //increment = parseFloat(increment);
            //var outcomeReference = $("option:selected #PMSOutComeId").data("reference");

           // var oldBasicPay = $("#old-basic-pay").val();
           // oldBasicPay = parseFloat(oldBasicPay);

           // var newBasicPay = 0;
          //  var changeInGrade = $("#Grade").val();
        //});
	
	$("#Grade").on("change", function () {
      	    var oldBasicPay = $("#old-basic-pay").val();
            oldBasicPay = parseFloat(oldBasicPay);
            var basicPay = $("option:selected", this).data("basepay");
            var lastpay = $("option:selected", this).data("lastpay");
            basicPay = parseFloat(basicPay);
            var increment = $("option:selected", this).data("increment");
            increment = parseFloat(increment);
            var newBasicPay = 0;
            var outcomeReference = $("option:selected #PMSOutComeId").data("reference");
            
	    if (basicPay - oldBasicPay < increment) {
               newBasicPay = basicPay + increment;
               while (
                 newBasicPay - oldBasicPay < increment &&
                 newBasicPay + increment <= lastpay
               ) {
          	newBasicPay = newBasicPay + increment;
               }
      	    } else {
              var newBasicPay = basicPay;
      	    }

            $("#BasicPay").val(newBasicPay.toFixed(0));
        });

        $("#FinalOutcomeId").on("change", function () {
            var value = $(this).val();
            if (value == 1) {
                $(".select-container").addClass("hide");
            } else {
                $(".select-container").removeClass("hide");
            }
        });

        if ($("#FinalOutcomeId").val() != "") {
            var value = $("#FinalOutcomeId").val();
            var outcomeId = value;
            var outcomeName = $("#FinalOutcomeId option:selected").text();
            if (value !== "") {
                $(".SavedPMSOutcomeId").each(function () {
                    if (value == $(this).val()) {
                        var curRow = $(this).closest("tr");
                        var employeeId = curRow.find(".emp-id").val();
                        console.log("check");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .attr("checked", "checked");
                        var checkTableExists = $(
                            "#outcome-" + outcomeId
                        ).length;
                        var clonedRow = curRow.clone();
                        clonedRow.find(".select-container").remove();
                        clonedRow.find(".remove-container").removeClass("hide");
                        clonedRow.find(".emp-id").removeAttr("disabled");
                        clonedRow.find(".submission-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").val(outcomeId);
                        if (checkTableExists == 0) {
                            var tableToClone = $("#table-to-clone");
                            var tableHtml =
                                '<input type="hidden" name="DeletedEmployeeIds" id="deleted-emp-ids"/><div class="row"><div class="col-md-12"><br/><h5 style="color:#fff;">Employees nominated for ' +
                                outcomeName +
                                '</h5><div class="sticky-columns table-responsive"><table id="outcome-' +
                                outcomeId +
                                '" class="sticky-columns table table-bordered table-striped table-hover table-condensed">';
                            var theader = tableToClone.find("thead").html();
                            tableHtml += "<thead>";
                            tableHtml += theader;
                            tableHtml += "</thead>";
                            tableHtml += "<tbody>";
                            tableHtml += "</tbody>";
                            tableHtml += "</table></div></div></div>";
                            $("#selected-employees").append(tableHtml);
                        }
                        $("#outcome-" + outcomeId)
                            .find("tbody")
                            .append(clonedRow);
                        $("#outcome-save-button").removeClass("hide");
                        $("#outcome-submit-button").removeClass("hide");
                    } else {
                        console.log("uncheck");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .removeAttr("checked");
                        var rowInSelected = $("#selected-employees")
                            .find(".emp-id[value='" + employeeId + "']")
                            .closest("tr");
                        var containerSelected =
                            rowInSelected.closest("div.row");
                        rowInSelected.remove();
                        if (containerSelected.find("tbody tr").length === 0) {
                            containerSelected.remove();
                        }
                    }
                });
            }
        }

        $(document).on("change", "#FinalOutcomeId", function () {
            var value = $("#FinalOutcomeId").val();
            var outcomeId = value;
            var outcomeName = $("#FinalOutcomeId option:selected").text();
            if (value !== "") {
                $(".SavedPMSOutcomeId").each(function () {
                    var curRow = $(this).closest("tr");
                    var employeeId = curRow.find(".emp-id").val();
                    if (value == $(this).val()) {
                        console.log("check");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .attr("checked", "checked");
                        var checkTableExists = $(
                            "#outcome-" + outcomeId
                        ).length;
                        var clonedRow = curRow.clone();
                        clonedRow.find(".select-container").remove();
                        clonedRow.find(".remove-container").removeClass("hide");
                        clonedRow.find(".emp-id").removeAttr("disabled");
                        clonedRow.find(".submission-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").removeAttr("disabled");
                        clonedRow.find(".outcome-id").val(outcomeId);
                        if (checkTableExists == 0) {
                            var tableToClone = $("#table-to-clone");
                            var tableHtml =
                                '<div class="row"><div class="col-md-12"><br/><h5 stydden" name="DeletedEmployeeIds" id="deleted-emp-ids"/>le="color:#fff;">Employees nominated for ' +
                                outcomeName +
                                '</h5><div class="sticky-columns table-responsive"><table id="outcome-' +
                                outcomeId +
                                '" class="sticky-columns table table-bordered table-striped table-hover table-condensed">';
                            var theader = tableToClone.find("thead").html();
                            tableHtml += "<thead>";
                            tableHtml += theader;
                            tableHtml += "</thead>";
                            tableHtml += "<tbody>";
                            tableHtml += "</tbody>";
                            tableHtml += "</table></div></div></div>";
                            $("#selected-employees").append(tableHtml);
                        }
                        $("#outcome-" + outcomeId)
                            .find("tbody")
                            .append(clonedRow);
                        $("#outcome-save-button").removeClass("hide");
                        $("#outcome-submit-button").removeClass("hide");
                    } else {
                        console.log("uncheck");
                        $(this)
                            .closest("td")
                            .find(".select-employee")
                            .removeAttr("checked");
                        var rowInSelected = $("#selected-employees")
                            .find(".emp-id[value='" + employeeId + "']")
                            .closest("tr");
                        var containerSelected =
                            rowInSelected.closest("div.row");
                        rowInSelected.remove();
                        if (containerSelected.find("tbody tr").length === 0) {
                            containerSelected.remove();
                        }
                    }
                });
            }
        });

        $(document).on("change", ".select-employee", function () {
            console.log("here");
            var curRow = $(this).closest("tr");
            var outcomeId = $("#FinalOutcomeId").val();
            var outcomeName = $("#FinalOutcomeId option:selected").text();
            var employeeId = curRow.find(".emp-id").val();
            if ($(this).is(":checked")) {
                var checkTableExists = $("#outcome-" + outcomeId).length;
                var clonedRow = curRow.clone();
                clonedRow.find(".select-container").remove();
                clonedRow.find(".remove-container").removeClass("hide");
                clonedRow.find(".emp-id").removeAttr("disabled");
                clonedRow.find(".submission-id").removeAttr("disabled");
                clonedRow.find(".outcome-id").removeAttr("disabled");
                clonedRow.find(".outcome-id").val(outcomeId);
                if (checkTableExists == 0) {
                    var tableToClone = $("#table-to-clone");
                    var tableHtml =
                        '<input type="hidden" name="DeletedEmployeeIds" id="deleted-emp-ids"/><div class="row"><div class="col-md-12"><br/><h5 style="color:#fff;">Employees nominated for ' +
                        outcomeName +
                        '</h5><div class="sticky-columns table-responsive"><table id="outcome-' +
                        outcomeId +
                        '" class="sticky-columns table table-bordered table-striped table-hover table-condensed">';
                    var theader = tableToClone.find("thead").html();
                    tableHtml += "<thead>";
                    tableHtml += theader;
                    tableHtml += "</thead>";
                    tableHtml += "<tbody>";
                    tableHtml += "</tbody>";
                    tableHtml += "</table></div></div></div>";
                    $("#selected-employees").append(tableHtml);
                }
                $("#outcome-" + outcomeId)
                    .find("tbody")
                    .append(clonedRow);
            } else {
                var rowInSelected = $("#selected-employees")
                    .find(".emp-id[value='" + employeeId + "']")
                    .closest("tr");
                var containerSelected = rowInSelected.closest("div.row");
                rowInSelected.remove();
                if (containerSelected.find("tbody tr").length === 0) {
                    containerSelected.remove();
                }
            }
            toggleSubmitButton();
        });

        $(document).on("click", ".remove-employee", function () {
            var curRow = $(this).closest("tr");
            var employeeId = curRow.find(".emp-id").val();
            var containerSelected = curRow.closest("div.row");
            curRow.remove();
            var oldValueOfHiddenField = $("#deleted-emp-ids").val();
            if (!oldValueOfHiddenField) {
                oldValueOfHiddenField = employeeId;
            } else {
                oldValueOfHiddenField += "," + employeeId;
            }
            $("#deleted-emp-ids").val(oldValueOfHiddenField);
            if (containerSelected.find("tbody tr").length === 0) {
                containerSelected.remove();
            }
            $("#table-to-clone .emp-id[value='" + employeeId + "']")
                .closest("tr")
                .find(".select-employee")
                .prop("checked", false);
            toggleSubmitButton();
        });

        var outcomeFormSubmitted = false;
        $(document).on("submit", "#outcome-form", function () {
            outcomeFormSubmitted = true;
        });

        $(document).on("click", "#outcome-save-button", function (e) {
            e.preventDefault();
            $("#outcome-form input[name='_token']").after(
                "<input type='hidden' name='SubmitType' value='1'/>"
            );
            $("#outcome-form").submit();
        });

        $(document).on("click", "#outcome-submit-button", function (e) {
            e.preventDefault();
            $("#outcome-form input[name='_token']").after(
                "<input type='hidden' name='SubmitType' value='2'/>"
            );
            $("#outcome-form").submit();
        });

        if ($("#table-to-clone").length) {
            $(window).bind("beforeunload", function () {
                if (outcomeFormSubmitted == false) {
                    if ($("#selected-employees table").length > 0) {
                        return "Are you sure you want to leave? Changes not saved will be lost!";
                    }
                }
            });
        }

        $(document).on("click", ".remove-required", function () {
            $("input[type!='hidden'], select").removeAttr("required");
        });

        $(document).on("click", ".back-btn", function (e) {
            e.preventDefault();
            history.back();
        });

        if (window.File && window.FileReader && window.Blob) {
            //SUPPORTS FILE APIs
            $(document).on(
                "change",
                "input[type='file']:not(.guideline-doc)",
                function () {
                    if ($(this).val()) {
                        var file = this.files[0];
                        var size = file.size;
                        var sizeInMB = size / 1024 / 1024;
                        if (sizeInMB > 5) {
                            $(this).val("");
                            $.alert(
                                "Error! Please select a file that is smaller than 5 MB in size"
                            );
                        }
                    }
                }
            );
        }
        if ($("[data-rel='lightcase']").length > 0) {
            $("[data-rel='lightcase']").lightcase();
        }
        $(document).on(
            "change",
            "#form-daterestriction #FromDate",
            function () {
                var fromDate = $(this).val();
                var toDate = $("#ToDate").val();
                if (fromDate != "") {
                    $("#ToDate").attr("min", fromDate);
                    if (toDate != "") {
                        var fromDateFormatted = new Date(fromDate);
                        var toDateFormatted = new Date(toDate);
                        if (fromDateFormatted > toDateFormatted) {
                            $("#ToDate").val("");
                        }
                    }
                }
            }
        );
        $(document).on("keyup", "#SearchBox", function () {
            var searchKeyword = $(this).val();
            searchKeyword = searchKeyword.toLowerCase();
            if (searchKeyword !== "") {
                $("#accordion .ui-accordion-header").each(function () {
                    var accordionMatch = false;
                    var curAccordion = $(this);
                    var accordionId = curAccordion.attr("id");
                    var curAccordionBody = $(
                        "[aria-labelledby='" + accordionId + "']"
                    );
                    curAccordionBody.find("tbody tr").each(function () {
                        var curRow = $(this);
                        var rowMatch = false;
                        curRow.find("td:not(:last-child)").each(function () {
                            var text = $(this).text();
                            text = text.toLowerCase();
                            if (text.indexOf(searchKeyword) > -1) {
                                accordionMatch = true;
                                rowMatch = true;
                            }
                        });
                        if (rowMatch === false) {
                            curRow.addClass("hide");
                        } else {
                            curRow.removeClass("hide");
                        }
                    });
                    if (accordionMatch === false) {
                        curAccordion.addClass("hide");
                        curAccordionBody.addClass("hide");
                    } else {
                        curAccordion.removeClass("hide");
                        curAccordionBody.removeClass("hide");
                    }
                });
            } else {
                $("#accordion .ui-accordion-header").removeClass("hide");
                $("#accordion .ui-accordion-content").removeClass("hide");
                $("#accordion tr").removeClass("hide");
            }
        });

        // $(document).on('keyup change click',".total-in-hidden",function(){
        //     var curRow = $(this).closest('tr');
        //     var figure = curRow.find('.figure');
        //     var total = 0;
        //     $(".total-in-hidden").each(function(){
        //         if($(this).val()!=''){
        //             total += parseFloat($(this).val());
        //         }
        //     });
        //     curRow.find(".figure").val(total);
        //
        //     var value = figure.val();
        //     var max = figure.attr('max');
        //     var min = figure.attr('min');
        //     var description = $(this).closest('tr').find('.description').text();
        //     if(parseFloat(value) > parseFloat(max)){
        //         figure.val('');
        //         $(this).val('');
        //         $.alert(description+" cannot have more than "+max+" points. Please enter correct value.");
        //     }else if(parseFloat(value) < parseFloat(min)) {
        //         figure.val('');
        //         $(this).val('');
        //         $.alert(description + " cannot have less than " + min + " points. Please enter correct value.");
        //     }
        //     calcTotal();
        // });
        $(".emailofficeorder").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            var empEmail = $(this).data("email");
            $.confirm({
                title: "Confirm email address!",
                content:
                    "" +
                    '<form action="" class="formName">' +
                    '<div class="form-group">' +
                    "<label>Please confirm email of employee</label>" +
                    '<input type="text" placeholder="Email" value="' +
                    empEmail +
                    '" class="email form-control" required />' +
                    "</div>" +
                    "</form>",
                buttons: {
                    formSubmit: {
                        text: "Submit",
                        btnClass: "btn-blue",
                        action: function () {
                            var email = this.$content.find(".email").val();
                            if (!email) {
                                $.alert("provide a valid email address");
                                return false;
                            }
                            window.location.href = href + "&email=" + email;
                        },
                    },
                    cancel: function () {
                        //close
                    },
                },
            });
        });
        $(document).on("change", ".filter-category", function () {
            var value = $(this).val();
            if (value !== "") {
                $(
                    ".category option[data-departmentid!='" +
                        value +
                        "'][value!='']"
                )
                    .addClass("hide")
                    .attr("disabled", "disabled");
                $(
                    ".category option[data-departmentid='" +
                        value +
                        "'][value!='']"
                )
                    .removeClass("hide")
                    .removeAttr("disabled");
            } else {
                $(".category option")
                    .removeClass("hide")
                    .removeAttr("disabled");
            }
            $(".category").val("");
        });
        $(document).on("change", ".filter-category-ajax", function () {
            var value = $(this).val();
            if (value !== "") {
                $.ajax({
                    url:
                        $("body").data("baseurl") +
                        "/fetchcategoriesondepartment",
                    dataType: "JSON",
                    type: "POST",
                    data: { deptId: value },
                    success: function (data) {
                        console.log(JSON.stringify(data));
                        $(".category option[value!='']").remove();
                        for (var x in data) {
                            $(".category").append(
                                "<option value='" +
                                    data[x].Id +
                                    "'>" +
                                    data[x].Name +
                                    "</option>"
                            );
                        }
                    },
                });
            } else {
                $(".category option[value!='']").remove();
            }
            $(".category").val("");
        });
        $(document).on("click", ".open-file-modal", function (e) {
            e.preventDefault();
            var file = $(this).data("filepath");
            var title = $(this).data("title");
            var baseUrl = $("body").data("baseurl");
            $("#file-iframe").attr(
                "src",
                baseUrl + "/filedisplay?file=" + file
            );
            $("#file-modal .modal-title").text(title);
            $("#file-modal").modal("show");
        });
        $(document).on("change", "#VisibilityLevel1", function () {
            var value = $(this).val();
            if (value == 99) {
                $("#VisibilityLevel").attr("disabled", "disabled").val("");
                $("#VisibilityLevel").select2("destroy");
                $("#VisibilityLevel").select2({
                    theme: "bootstrap4",
                    placeholder: "--SELECT--",
                });
            } else {
                $("#VisibilityLevel").removeAttr("disabled");
            }
        });
        $(document).on("click", ".upload-kpi-template", function () {
            var type = $(this).data("type");
            var file = $(this).closest(".row").find(".kpi-file")[0];
            var formData = new FormData();
            formData.append("file", file.files[0]);
            formData.append("_token", $("input[name='_token']").val());
            formData.append("type", type);
            $.ajax({
                url: "/uploadkpifile",
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (data) {
                    $("tbody[data-type='" + type + "']").html(data);
                },
            });
        });
        $(document).on("change", ".clear-pms-period", function () {
            if ($(this).val() != "") {
                $("#PMSPeriod").val("");
                $("#PMSPeriod").select2("destroy");
                $("#PMSPeriod").select2({
                    theme: "bootstrap4",
                    placeholder: "All",
                });
            }
        });
        $(document).on("select2:select", "#PMSPeriod", function () {
            console.log("here");
            if ($(this).val() != "") {
                $(".clear-pms-period").val("");
                $(".clear-pms-period").select2("destroy");
                $(".clear-pms-period").select2({
                    theme: "bootstrap4",
                    placeholder: "All",
                });
            }
        });

        // selecting and deleting goals and targets for subordinate by supervisor
        $("#goal-approve-button").addClass("hide");
        $("#goal-resend-button").addClass("hide");
        $("#goal-delete-button").addClass("hide");

        var uncheckedgoals = $("#uncheckedgoals").val();
        var uncheckedtasks = $("#uncheckedtasks").val();

        var uncheckedgoalswithmarkingsubmittedbyself = $(
            "#uncheckedgoalswithmarkingsubmittedbyself"
        ).val();
        var uncheckedgoalswithmarkingsubmittedbysupervisor = $(
            "#uncheckedgoalswithmarkingsubmittedbysupervisor"
        ).val();
        var uncheckedgoalswithnomarkingsubmittedbyself = $(
            "#uncheckedgoalswithnomarkingsubmittedbyself"
        ).val();
        var uncheckedgoalswithnomarkingsubmittedbysupervisor = $(
            "#uncheckedgoalswithnomarkingsubmittedbysupervisor"
        ).val();

        $("#select_all_goalIds").show();
        $("#select_all_taskIds").show();

        if (
            uncheckedgoals > 0 ||
            uncheckedtasks > 0 ||
            uncheckedgoalswithmarkingsubmittedbyself > 0 ||
            uncheckedgoalswithmarkingsubmittedbysupervisor > 0 ||
            uncheckedgoalswithnomarkingsubmittedbyself > 0 ||
            uncheckedgoalswithnomarkingsubmittedbysupervisor > 0
        ) {
            $("#select_all_goalIds").show();
            $("#select_all_taskIds").show();
        }

        $(".selected_goalIds").on("change", function () {
            var goalIds = [];
            $("input:checkbox[name=goalIds]:checked").each(function () {
                goalIds.push($(this).val());
            });
            if (goalIds.length > 0) {
                $("#goal-approve-button").removeClass("hide");
                $("#goal-resend-button").removeClass("hide");
                $("#goal-delete-button").removeClass("hide");
            } else {
                $("#goal-approve-button").addClass("hide");
                $("#goal-resend-button").addClass("hide");
                $("#goal-delete-button").addClass("hide");
            }
        });

        $("#select_all_goalIds").on("click", function () {
            var goalIds = [];
            $(".selected_goalIds").prop("checked", $(this).prop("checked"));
            $("input:checkbox[name=goalIds]:checked").each(function () {
                goalIds.push($(this).val());
            });

            if (goalIds.length > 0) {
                $("#goal-approve-button").removeClass("hide");
                $("#goal-resend-button").removeClass("hide");
                $("#goal-delete-button").removeClass("hide");
            } else {
                $("#goal-approve-button").addClass("hide");
                $("#goal-resend-button").addClass("hide");
                $("#goal-delete-button").addClass("hide");
            }
        });

        $("#goal-accept-button").addClass("hide");
        $("#goal-reject-button").addClass("hide");

        $(".selected_taskIds").on("change", function () {
            var taskIds = [];
            $("input:checkbox[name=taskIds]:checked").each(function () {
                taskIds.push($(this).val());
            });
            if (taskIds.length > 0) {
                $("#goal-accept-button").removeClass("hide");
                $("#goal-reject-button").removeClass("hide");
            } else {
                $("#goal-accept-button").addClass("hide");
                $("#goal-reject-button").addClass("hide");
            }
        });

        $("#select_all_taskIds").on("click", function () {
            var taskIds = [];
            $(".selected_taskIds").prop("checked", $(this).prop("checked"));
            $("input:checkbox[name=taskIds]:checked").each(function () {
                taskIds.push($(this).val());
            });

            if (taskIds.length > 0) {
                $("#goal-accept-button").removeClass("hide");
                $("#goal-reject-button").removeClass("hide");
            } else {
                $("#goal-accept-button").addClass("hide");
                $("#goal-reject-button").addClass("hide");
            }
        });

        // delete goals ids
        $("#goal-delete-button").on("click", function (e) {
            e.preventDefault();
            $.confirm({
                title: "Confirm your action",
                content:
                    "Are you sure you want to delete these records? This action cannot be undone!",
                buttons: {
                    Yes: function () {
                        var message = "";
                        let goalIds = [];
                        $("input:checkbox[name=goalIds]:checked").each(
                            function () {
                                goalIds.push($(this).val());
                            }
                        );

                        if (goalIds.length > 0) {
                            $.ajax({
                                type: "POST",
                                url: "/deletemultiplegoalsandtargets",
                                data: { goalIds: goalIds },
                                success: function (response) {
                                    if (response == 1) {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets has been deleted successfully.</strong>"
                                        );
                                    } else if (response == 2) {
                                        $.alert(
                                            message +
                                                "<strong>Goal & Target has been deleted successfully.</strong>"
                                        );
                                    } else {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets cannot be deleted.</strong>"
                                        );
                                    }

                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                                },
                            });
                        } else {
                            location.reload();
                        }
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // approve goals ids
        $("#goal-approve-button").on("click", function (e) {
            e.preventDefault();
            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to approve these records?",
                buttons: {
                    Yes: function () {
                        var message = "";
                        let goalIds = [];
                        $("input:checkbox[name=goalIds]:checked").each(
                            function () {
                                goalIds.push($(this).val());
                            }
                        );
                        if (goalIds.length > 0) {
                            $.ajax({
                                type: "POST",
                                url: "/approvemultiplegoalsandtargets",
                                data: { goalIds: goalIds },
                                success: function (response) {
                                    if (response == 1) {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets has been approved successfully.</strong>"
                                        );
                                    } else if (response == 2) {
                                        $.alert(
                                            message +
                                                "<strong>Goal & Target has been approved successfully.</strong>"
                                        );
                                    } else {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets cannot be approved.</strong>"
                                        );
                                    }

                                    

                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                                },
                            });
                        } else {
                            location.reload();
                        }
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // resend goals ids
        $("#goal-resend-button").on("click", function (e) {
            e.preventDefault();
            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to resend these records?",
                buttons: {
                    Yes: function () {
                        var message = "";
                        let goalIds = [];
                        $("input:checkbox[name=goalIds]:checked").each(
                            function () {
                                goalIds.push($(this).val());
                            }
                        );
                        if (goalIds.length > 0) {
                            $.ajax({
                                type: "POST",
                                url: "/resendmultiplegoalsandtargets",
                                data: { goalIds: goalIds },
                                success: function (response) {
                                    if (response == 1) {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets has been resend successfully.</strong>"
                                        );
                                    } else if (response == 2) {
                                        $.alert(
                                            message +
                                                "<strong>Goal & Target has been resend successfully.</strong>"
                                        );
                                    } else {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets cannot be resend.</strong>"
                                        );
                                    }

                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                                },
                            });
                        } else {
                            location.reload();
                        }
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // accept and publish goal ids
        $("#goal-accept-button").on("click", function (e) {
            e.preventDefault();
            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to accept these goals?",
                buttons: {
                    Yes: function () {
                        var message = "";
                        let taskIds = [];
                        $("input:checkbox[name=taskIds]:checked").each(
                            function () {
                                taskIds.push($(this).val());
                            }
                        );
                        if (taskIds.length > 0) {
                            $.ajax({
                                type: "POST",
                                url: "/acceptmultiplegoaltargets",
                                data: { taskIds: taskIds },
                                success: function (response) {
                                    if (response == 1) {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets has been accepted successfully.</strong>"
                                        );
                                    } else if (response == 2) {
                                        $.alert(
                                            message +
                                                "<strong>Goal & Target has been accepted successfully.</strong>"
                                        );
                                    } else {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets cannot be accepted.</strong>"
                                        );
                                    }

                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                                },
                            });
                        } else {
                            location.reload();
                        }
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // reject or delete goal ids
        $("#goal-reject-button").on("click", function (e) {
            e.preventDefault();
            $.confirm({
                title: "Confirm your action",
                content:
                    "Are you sure you want to reject these records? This action cannot be undone later!",
                buttons: {
                    Yes: function () {
                        var message = "";
                        let taskIds = [];
                        $("input:checkbox[name=taskIds]:checked").each(
                            function () {
                                taskIds.push($(this).val());
                            }
                        );
                        if (taskIds.length > 0) {
                            $.ajax({
                                type: "POST",
                                url: "/rejectmultiplegoaltargets",
                                data: { taskIds: taskIds },
                                success: function (response) {
                                    if (response == 1) {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets has been rejected successfully.</strong>"
                                        );
                                    } else if (response == 2) {
                                        $.alert(
                                            message +
                                                "<strong>Goal & Target has been rejected successfully.</strong>"
                                        );
                                    } else {
                                        $.alert(
                                            message +
                                                "<strong>Goals & Targets cannot be rejected.</strong>"
                                        );
                                    }

                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000);
                                },
                            });
                        } else {
                            location.reload();
                        }
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // remove editing goal targets
        $(".goal-target-details").show().find("input").removeAttr("required");

        // file document for both goal & task monitoring and revision
        $(".file-upload-document").hide().find("input").removeAttr("required");
        var goalstatusId = $(".goal-status-id").val();
        if (goalstatusId == 1) {
            // Updated
            $(".goal-update").show().find("input").attr("required");
            $(".goal-reassign").show().find("input").removeAttr("required");
            $(".goal-reappropriate")
                .show()
                .find("input")
                .removeAttr("required");
            $(".file-upload-document")
                .hide()
                .find("input")
                .removeAttr("required");
        } else if (goalstatusId == 2) {
            // Reassigned
            $(".goal-update").show().find("input").removeAttr("required");
            $(".goal-reassign").show().find("input").attr("required");
            $(".goal-reappropriate")
                .show()
                .find("input")
                .removeAttr("required");
            $(".file-upload-document")
                .hide()
                .find("input")
                .removeAttr("required");
        } else if (goalstatusId == 3) {
            // Reappropriated
            $(".goal-update").show().find("input").removeAttr("required");
            $(".goal-reassign").show().find("input").removeAttr("required");
            $(".goal-reappropriate").show().find("input").attr("required");
            $(".file-upload-document").show().find("input").attr("required");
        }

        $(".goal-status-id").on("change", function () {
            goalstatusId = $(".goal-status-id").val();
            if (goalstatusId == 1) {
                // Updated
                $(".goal-update").show().find("input").attr("required");
                $(".goal-reassign").show().find("input").removeAttr("required");
                $(".goal-reappropriate")
                    .show()
                    .find("input")
                    .removeAttr("required");
                $(".file-upload-document")
                    .hide()
                    .find("input")
                    .removeAttr("required");
            } else if (goalstatusId == 2) {
                // Reassigned
                $(".goal-update").show().find("input").removeAttr("required");
                $(".goal-reassign").show().find("input").attr("required");
                $(".goal-reappropriate")
                    .show()
                    .find("input")
                    .removeAttr("required");
                $(".file-upload-document")
                    .hide()
                    .find("input")
                    .removeAttr("required");
            } else if (goalstatusId == 3) {
                // Reappropriated
                $(".goal-update").show().find("input").removeAttr("required");
                $(".goal-reassign").show().find("input").removeAttr("required");
                $(".goal-reappropriate").show().find("input").attr("required");
                $(".file-upload-document")
                    .show()
                    .find("input")
                    .attr("required");
            }
        });

        // adhoc goals hide weightage when has no marking
        var hasMarking = 1;
        hasMarking = $(".adhoc-has-marking").val();
        if (hasMarking == 0) {
            $(".show-weightage").hide().find("input").removeAttr("required");
            $(".goal-weightage-total").val(0);
            $(".task-weightage").val(0.1);
        } else {
            $(".show-weightage").show().find("input").attr("required");
        }

        $(".adhoc-has-marking").on("change", function () {
            hasMarking = $(".adhoc-has-marking").val();
            if (hasMarking == 0) {
                $(".show-weightage")
                    .hide()
                    .find("input")
                    .removeAttr("required");
                $(".goal-weightage-total").val(0);
                $(".task-weightage").val(0.1);
            } else {
                $(".show-weightage").show().find("input").attr("required");
            }
        });

	// self rating calculate
        $(document).on("keyup keydown change", ".self-rating-score", function () {
            var table = $(this).closest("table");
            var tableTotal = 0;
            table.find(".self-rating-score").each(function () {
                tableTotal +=
                    $(this).val() === "" ? 0 : parseFloat($(this).val());
            });
            table.find(".self-rating-total").val(tableTotal.toFixed(2));
        });

        // level 1 rating calculate
        $(document).on("keyup keydown change", ".level1-rating-score", function () {
            var table = $(this).closest("table");
            var tableTotal = 0;
            table.find(".level1-rating-score").each(function () {
                tableTotal +=
                    $(this).val() === "" ? 0 : parseFloat($(this).val());
            });
            table.find(".level1-rating-total").val(tableTotal.toFixed(2));
            $("#total-level-1-score").val(tableTotal.toFixed(2));
        });

        // level 2 rating calculate
        $(document).on("keyup keydown change", ".level2-rating-score", function () {
            var table = $(this).closest("table");
            var tableTotal = 0;
            table.find(".level2-rating-score").each(function () {
                tableTotal +=
                    $(this).val() === "" ? 0 : parseFloat($(this).val());
            });
            table.find(".level2-rating-total").val(tableTotal.toFixed(2));
            $("#total-level-2-score").val(tableTotal.toFixed(2));
        });

        $(".withdrawal-confirm").on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");

            $.confirm({
                title: "Confirm your action",
                content: "Are you sure you want to withdrawal the employee?",
                buttons: {
                    Yes: function () {
                        window.location.href = href;
                    },
                    No: {
                        text: "No",
                        btnClass: "btn-blue",
                        keys: ["enter", "shift"],
                        action: function () {},
                    },
                },
            });
        });

        // end
    }
    return {
        Initialize: initialize,
        ToggleSubmitButton: toggleSubmitButton,
    };
})();
$(document).ready(function () {
    script.Initialize();
});
