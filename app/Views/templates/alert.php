


<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">


<script type="text/javascript">
     $(function() {
      $(document).Toasts('create', {
        class: 'bg-success',
        title: 'Toast Title',
        subtitle: 'Subtitle',
        body: 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr.'
      })
    });
    
<?php if(session()->getFlashdata('success')){ ?>
    toastr.success("<?php echo session()->getFlashdata('success'); ?>");
<?php }else if(session()->getFlashdata('error')){  ?>
    toastr.error("<?php echo session()->getFlashdata('error'); ?>");
<?php }else if(session()->getFlashdata('warning')){  ?>
    toastr.warning("<?php echo session()->getFlashdata('warning'); ?>");
<?php }else if(session()->getFlashdata('info')){  ?>
    toastr.info("<?php echo session()->getFlashdata('info'); ?>");
<?php } ?>


</script>
