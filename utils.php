<?php

// Removes empty strings from array
function array_clean( &$a ){
	foreach( $a as $key => $value ){
		if( empty( $value ) )
			unset( $a[$key] );
	}
}

