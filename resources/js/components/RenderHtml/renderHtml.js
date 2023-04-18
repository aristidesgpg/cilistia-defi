import htmlParser from "html-react-parser";
import DOMPurify from "dompurify";

const RenderHtml = ({html}) => {
    if (!DOMPurify.isSupported) {
        return null;
    }

    const sanitized = DOMPurify.sanitize(html, {
        USE_PROFILES: {html: true}
    });

    return htmlParser(sanitized);
};

export default RenderHtml;
