import React, {useCallback, useEffect, useState} from "react";
import {useParams} from "react-router-dom";
import {errorHandler, route, useRequest} from "services/Http";
import Result404 from "components/Result404";
import {UserProvider} from "contexts/UserContext";
import Content from "./components/Content";
import User from "models/User";
import LoadingFallback from "components/LoadingFallback";

const Profile = () => {
    const {name} = useParams();
    const [request, loading] = useRequest();
    const [user, setUser] = useState();

    const fetchUser = useCallback(() => {
        request
            .get(route("user-profile.get", {user: name}))
            .then((data) => setUser(User.use(data)))
            .catch(errorHandler());
    }, [request, name]);

    useEffect(() => {
        fetchUser();
    }, [fetchUser]);

    return (
        <LoadingFallback
            content={user}
            fallback={<Result404 />}
            compact={true}
            loading={loading}
            size={70}>
            {(user) => (
                <UserProvider user={user} fetchUser={fetchUser}>
                    <Content />
                </UserProvider>
            )}
        </LoadingFallback>
    );
};

export default Profile;
