import {useCallback, useState} from "react";
import {uniqueId} from "lodash";

export function useUniqueId(prefix = "unique_") {
    const [id] = useState(() => uniqueId(prefix));

    return useCallback((name) => (name ? `${id}_${name}` : id), [id]);
}
