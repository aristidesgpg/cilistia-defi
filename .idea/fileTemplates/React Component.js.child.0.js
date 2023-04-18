#set($EXPORT = $NAME.substring(0,1).toLowerCase() + $NAME.substring(1))
export * from "./$EXPORT";
export {default} from "./$EXPORT";