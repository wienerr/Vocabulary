<?php
/**
* Template Name: vocalize_setup
*
* This is a one page dedicated template. 
* it is a front end form used to set parameters for a site
* used for language vocabulary learning.
* This page is only accessed by logined users.
* The styling is bootstrap.
* The theme is Neve. 
* 
* The user specific data in the user meta table.
* some of the data which is used interactively by JS is using the WP localizer facility.
* The speech voice and language parameter is stored in the localStorage
* since it is unique to a device and a browser.
*/
// from here my template code (not the theme)
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
// Post the submitted variables in the current user meta.
if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == "front_post" && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {
	// if security OK store our post vars into variables for later use
		$tzoffset=sanitize_textarea_field( $_POST['tzoffset']);
		$maxtexts=sanitize_textarea_field( $_POST['maxtexts']);
		$dicu=sanitize_textarea_field( $_POST['dicu']);
		$srate=sanitize_textarea_field( $_POST['rate-label']);
		$spitch=sanitize_textarea_field( $_POST['pitch-label']);
		$sdelay=sanitize_textarea_field( $_POST['delay-label']);
	// Store the setup variables in user meta.
		$uid=get_current_user_id();
		$rslt1=update_user_meta( $uid, 'tzoffset', $tzoffset );
		$rslt2=update_user_meta( $uid, 'maxtexts', $maxtexts );
		$rslt3=update_user_meta( $uid, 'dicu', $dicu );
		$rslt4=update_user_meta( $uid, 'srate', $srate );
		$rslt5=update_user_meta( $uid, 'spitch', $spitch );
		$rslt6=update_user_meta( $uid, 'sdelay', $sdelay );
		exit;
}
// end of my code
// from here the themes page code	
$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-page' );

get_header();

?>
<div class="<?php echo esc_attr( $container_class ); ?> single-page-container">
	<div class="row">
		<?php do_action( 'neve_do_sidebar', 'single-page', 'left' ); ?>
		<div class="nv-single-page-wrap col">
			<?php
			/**
			 * Executes actions before the page header.
			 *
			 * @since 2.4.0
			 */
			do_action( 'neve_before_page_header' );

			/**
			 * Executes the rendering function for the page header.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			do_action( 'neve_page_header', 'single-page' );

			/**
			 * Executes actions before the page content.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			/* the page content*/
// up to here is the themes page code
// from here my form  code 
?>


<?php
// Get the data from the user meta for populating the form
// the get functions are in the vocabulary plugin
	$uid=get_current_user_id();
	$maxtexts=get_max_txts();
	$dicu=get_dicu();
	$srate=get_srate();
	$spitch=get_spitch();
	$sdelay=get_sdelay();
?>
<div class="front-form col-sm-6">
  <form method="post" name="front_end" action="" >
    <input type="hidden" id="tzoffset" name="tzoffset"> 
    <label for="maxtexts" style="font-size:18px">Maximum number of texts displayed each day</label>
    <input type="text" placeholder="Maximum number of texts displayed each day" name="maxtexts" value="<?php echo $maxtexts; ?>"   required  />
    <label for="dicu" style="font-size:18px">URL of the additional dictionary (%word% is replaced with the text translated)</label>
    <input type="text" placeholder="URL of additional dictionary" name="dicu"  value="<?php echo $dicu ?>" />

    <h1 >Text to Speech</h1>
    <p >Select Voice & Language</p>
    <!-- Select Menu for Voice -->
    This may change when you change device or browser. Best works on Google Chrome.
    <select name="voices" id="voices" class="form-select bg-secondary text-light"></select>
    <br>
    <!-- Range Slliders for Volume, Rate & Pitch -->
    
    <table>
      <tbody>   
        <tr>
          <td>    
            <div class="lead"  >
              <p class="lead">speech speed </p>
              <input type="range" min="0.1" max="1.5" value="<?php echo $srate ?>" id="rate" step="0.1" />
              <input style="text-align: center;" type='text' name="rate-label" id="rate-label" class="ms-2" value='<?php echo $srate ?>' readonly >
            </div>
          </td>
          <td>
            <div  >
              <p class="lead">Voice Pitch</p>
              <input type="range" min="0" max="2" value="<?php echo $spitch ?>" step="0.1" id="pitch" />
              <input style="text-align: center;" type='text' name="pitch-label" id="pitch-label" class="ms-2" value='<?php echo $spitch ?>' readonly >
            </div>
          </td>
          <td>
            <div>
              <p class="lead">delay (sec)</p>
              <input type="range" min="0" max="4" value="<?php echo $sdelay ?>" step="0.1" id="delay" />
              <input style="text-align: center;" type='text' name="delay-label" id="delay-label" class="ms-2" value='<?php echo $sdelay ?>' readonly >
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="3">
            <p class="lead">Test the speech</p>
            <input type="text" class="form-control bg-dark text-light mt-5" placeholder="Type here..."></textarea>
            <button id="start"  type=button class="btn btn-success mt-5 me-3">Speak</button>
            <button id="cancel"  type=button class="btn btn-danger mt-5 me-3">Stop</button>
          </td>
        </tr>
      </tbody>
    </table>
    <br>
    <br>
    <button type="submit">Submit</button>
    <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
    <input type="hidden" name="action" value="front_post" />
  </form>
</div>
<!--end of my form code-->
<!-- from here them page code -->
			
           <?php
			/**
			 * Executes actions after the page content.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			do_action( 'neve_after_content', 'single-page' );
			?>
		</div>
		<?php do_action( 'neve_do_sidebar', 'single-page', 'right' ); ?>
	</div>
</div>
<?php get_footer(); ?>
<script>
jQuery( document ).ready( function($) {
	//text to speach settings 
	// Get List of Voices
	let speech = new SpeechSynthesisUtterance();
	window.speechSynthesis.onvoiceschanged = () => {
    	voices = window.speechSynthesis.getVoices();
    	// Initially set the First Voice in the Array.
    	// Set the Voice Select List. (Set the Index as the value, which we'll use later when the user updates the Voice using the Select Menu.)
    	let voiceSelect = document.querySelector("#voices");
    	voiceSelect.options[0] = new Option('Select voice & language', -1);
    	voices.forEach((voice, i) => {(voiceSelect.options[i] = new Option(voice.name, i))
    	});   
 	   svoice=localStorage.getItem("voice");
	    if (!svoice) {
        	localStorage.setItem("voice", voices[0].name);
        	voiceSelect.value=0;
    	}else{ 
 			voices.forEach((voice, i) =>{ if (voice.name==svoice) idx=i;
			});
			voiceSelect.value=idx;
		}
	}
	// each of the variables other than the voice is stored on submit 
	// in the user meta using the localizer function.
	//
	document.querySelector("#rate").addEventListener("input", () => {
 	 	// Get rate Value from the input
  		const rate = document.querySelector("#rate").value;
		speech.rate = rate;
  		// Update the rate label
  		document.querySelector("#rate-label").value = rate;
	});

	document.querySelector("#pitch").addEventListener("input", () => {
  		// Get pitch Value from the input
  		const pitch = document.querySelector("#pitch").value;
   		speech.pitch = pitch;
  		// Update the pitch label
  		document.querySelector("#pitch-label").value = pitch;
	});

	document.querySelector("#delay").addEventListener("input", () => {
  		// Get pitch Value from the input
   		const delay = document.querySelector("#delay").value;
  		// Update the pitch label
	  	document.querySelector("#delay-label").value = delay;
	});
	// The voice is unique to each device hence it is store
	// in the localstorage. It is stored each time it changes and 
	// not on submit.
	document.querySelector("#voices").addEventListener("change", () => {
	    var lst= document.querySelector("#voices");      
    	var stxt=lst[lst.value].text;
        // alert(stxt);
      	localStorage.setItem("voice", stxt);
	});
	// the speech start button
    document.querySelector("#start").addEventListener("click", () => {
		// verify there is something to say	
		if (document.querySelector(".form-control").value==''){
    		alert('Please enter text to say'); 
		    window.speechSynthesis.cancel();
    		return false;//this will abort the script
		} 
		// initialize the speech object
	  	speech.text = document.querySelector(".form-control").value;
  		speech.rate = document.querySelector("#rate").value;
	  	speech.pitch = document.querySelector("#pitch").value;
	  	txt = localStorage.getItem("voice");//the name of the voice is retrieved.
		// next we find the index of the voice object with the name retrieved.  
	  	idx=0;
  		voices.forEach((voice, i) =>{ if (voice.name==txt) idx=i;});
		speech.voice=voices[idx];
		// this is a delay for the user to escape the speech on the 
		// page of the list of texts being reviewed.
		// the value on the slider is in sec. The sleep function below
		// is in microseconds
		delay = document.querySelector("#delay").value*1000;
		sleep(delay);
  		// Start Speaking
  		window.speechSynthesis.speak(speech);
	});
	document.querySelector("#cancel").addEventListener("click", () => {
  		// Cancel the speechSynthesis instance
  		window.speechSynthesis.cancel();
	});

	// finally reseting.
  	window.speechSynthesis.cancel();
});// End of the document.onready 

function sleep(num) {
    var now = new Date();
    var stop = now.getTime() + num;
    while(true) {
        now = new Date();
        if(now.getTime() > stop) return;
    }
}
// end of the speech portion.

jQuery( document ).ready( function($) {
  // get the users brauser tiemzone. Set the difference (in seconds)
  // in the hidden input field. It will be stored as user meta on submit
  // using the localizer function.
  var d = new Date();
  var tzdiff = -60*(d.getTimezoneOffset());
  $("#tzoffset").val(tzdiff);
});   

 //for mobile device needs to change orientation
	
//This code reloads the page when the orientation changes	
jQuery(window).on("orientationchange",function(){
	location.reload();
});

// this code when the page loaded measures the state of the screen
// if it is portrait it alerts to change to landscape and hides the content.
if(screen.availHeight > screen.availWidth){
    jQuery( ".content" ).css( "display", "none" );
	var newLine = "\r\n"
    var msg = "Please use Landscape!"
    msg += newLine;
    msg += "(Turn your phone 90 degrees)";
    msg += newLine;
    msg += "and wait a second,";	
    msg += newLine;
    msg += "for the page to reload.";	
    alert(msg);
}
	
</script>
