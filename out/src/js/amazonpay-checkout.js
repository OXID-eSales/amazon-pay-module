;(function() {
    $(document).ready(function () {
        $('#checkAgbTop').on('click', function (event){
            let $checkbox = $(event.target),
                confirm = 0;

            if ($checkbox.is(':checked')) {
                confirm = 1;
            }

            $.ajax({
                type: "POST",
                url: "/index.php?cl=order&fnc=confirmAGB",
                data: {confirm: confirm}
            });
        })
    })
})()