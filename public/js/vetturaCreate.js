$(document).ready(function () {

    NS_VETTURA_CREATE.setEvents();

});

var NS_VETTURA_CREATE = {

    setEvents: function () {

        $("#oggiBtn").click(function () {

            NS_VETTURA_CREATE.setDateRevisione();
        });

        $("input").change(function () {

            var valore = ($(this).val()).toUpperCase();
            $(this).val(valore);
        });

        $("#app_bundle_vettura_type_targa").change(function () {

            NS_VETTURA_CREATE.ajaxGetTarga($(this));

        });
    },

    setDateRevisione : function(){

        var fullDate = new Date();

        var oggi = fullDate.getFullYear() + "-" + NS_VETTURA_CREATE.getFormatMonth(fullDate.getMonth()) + "-" + NS_VETTURA_CREATE.getFormatDay(fullDate.getDate());

        var d = new Date(fullDate.getFullYear() + 2, fullDate.getMonth(), fullDate.getDate());

        var scadenza = d.getFullYear() + "-" + NS_VETTURA_CREATE.getFormatMonth(d.getMonth()) + "-" + NS_VETTURA_CREATE.getFormatDay(d.getDate());

        $("#app_bundle_vettura_type_dataUltimaRevisione").val(oggi.toString());
        $("#app_bundle_vettura_type_dataScadenzaRevisione").val(scadenza.toString());
    },

    getFormatDay : function(day){
        return (((day.toString().length) === 2) ? (day) : ('0' + (day)));
    },

    getFormatMonth : function(month) {
        return (((month.toString().length) === 2) ? (month + 1) : ('0' + (month + 1)));
    },

    ajaxGetTarga: function (el) {

        var targa = el.val();
        var path = $("#divTargaPath").attr("data-path");

        $.ajax({
            type: "POST",
            url: path,
            data: {"targa": targa},
            async: true,
            dataType: "json",
            success: function (json) {
                if (json.status === "KO") {
                    $("#errorModal").modal("show");
                    el.val("");
                }
            }
        })
    }

};