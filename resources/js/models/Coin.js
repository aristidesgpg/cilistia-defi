import Model from "models/Model";

export default class Coin extends Model {
    /**
     * Get svg icon
     *
     * @returns {String}
     */
    svgIcon() {
        return this.get("svg_icon");
    }
}
