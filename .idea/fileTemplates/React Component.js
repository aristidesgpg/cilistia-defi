#set($COMPONENT = $NAME.substring(0,1).toUpperCase() + $NAME.substring(1))
import React from 'react';

const $COMPONENT = () => {
 return (
  <div>
   #[[ $END$ ]]#
  </div>
 );
};

export default $COMPONENT;