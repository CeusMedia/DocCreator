var smalls	= "abcdefghijklmnopqrstuvwxyzäöüßâáàêéèîíìôóòûúù";
var larges	= "ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜÂÁÀÊÉÈÎÍÌÔÓÒÛÚÙ";
var digit	= "1234567890";
var symbols 	= "+-*/%=;()[]{}^²³'`´~§$&'?!#\\µ€";
var dot		= ".";
var comma	= ",";
var colon	= ":";
var semicolon	= ";";
var hyphen	= "-";
var underscore	= "_";
var space	= " ";
var at		= "@";

var classes	= {
  'numeric'	:digit,
  'dotnumeric'	:digit+comma+dot,
  'float'	:digit+dot,
  'letter'	:larges+smalls,
  'alpha'	:larges+smalls+digit,
  'alphasymbol'	:larges+smalls+digit+symbols+at+dot+comma+colon+hyphen+underscore,
  'alphahyphen'	:larges+smalls+digit+hyphen+underscore,
  'alphaspace'	:larges+smalls+digit+space,
  'email'	:larges+smalls+digit+at+dot+hyphen+underscore,
  'emails'	:larges+smalls+digit+at+dot+hyphen+underscore+semicolon,
  'id'		:larges+smalls+digit+dot+hyphen+underscore+colon+at,
  'all'		:larges+smalls+digit+symbols+at+dot+comma+colon+hyphen+underscore+space
};

function allowOnly( element, type )
{
  var value = element.value;
  var len = value.length;
  var legal;
  if( legal = classes[type] )
  {
    for( var i=0; i<len; i++ )
    {
      var sign = value.substr( i, 1 );
      if( legal.indexOf( sign ) < 0 )
      {
        value = value.substr( 0, i ) + value.substring( i + 1 );
        len --;
        i--;
      }
    }
    if( element.value != value )
      element.value = value;
  }
}