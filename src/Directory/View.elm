module View exposing (view)

import Date exposing (Date)
import Date.Distance
import Date.Format
import Json.Decode as Decode
import Html exposing (Html, text)
import Html.Attributes exposing (class, src, alt, href)
import Html.Events exposing (onClick, onWithOptions, defaultOptions)
import Communities exposing (..)
import Messages exposing (Msg(..))
import Model exposing (Model)
import Pagination exposing (Pagination)
import Routing exposing (Route(..), reverse)


navigateLink : Route -> String -> String -> Html Msg
navigateLink route classes content =
    let
        onClickNoDefault =
            onWithOptions "click"
                { defaultOptions | preventDefault = True }
                (Decode.succeed <| NavigateTo route)
    in
        Html.a [ href <| reverse route, class classes, onClickNoDefault ]
            [ text content ]


maybeHtml : (a -> Html msg) -> Maybe a -> Html msg
maybeHtml viewFunction =
    Maybe.map viewFunction >> Maybe.withDefault (text "")


view : Model -> Html Msg
view { communities, currentDate } =
    let
        communitiesHtml =
            if Pagination.isLoading communities then
                Html.div [ class "loading my-4" ]
                    [ Html.div [ class "text-primary text-center" ] [ text "Loading the latest Communities, please wait..." ]
                    , Html.div [ class "progress align-middle" ]
                        [ Html.div [ class "progress-bar progress-bar-striped progress-bar-animated w-100" ] [] ]
                    ]
            else if Pagination.getError communities /= Nothing then
                Html.div [ class "text-danger text-center" ]
                    [ text "Sorry, we encountered a problem when trying to load Communities, please try again or contact "
                    , Html.a [ href "mailto:directory@ic.org" ] [ text "directory@ic.org" ]
                    , text "."
                    ]
            else
                Html.div [ class "list-group directory-listings mt-2" ] <|
                    List.map (communityItem currentDate) <|
                        Pagination.getCurrent communities
    in
        Html.div []
            [ links
            , resultCount communities
            , communitiesHtml
            , if not <| Pagination.hasNone communities then
                Html.div []
                    [ links
                    , Html.ul [ class "pagination justify-content-center" ]
                        (pagination communities)
                    ]
              else
                text ""
            ]


links : Html msg
links =
    Html.div [ class "directory-header-links" ] <|
        List.intersperse (text " | ") <|
            List.map
                (\( content, slug ) ->
                    Html.a [ href <| "/directory/" ++ slug ] [ text content ]
                )
                [ ( "Newest Communities", "newest-communities" )
                , ( "Recently Updated", "recently-updated" )
                , ( "Types", "community-types" )
                , ( "State/Country List", "intentional-communities-by-country" )
                , ( "Maps", "map" )
                , ( "Advanced Search", "search" )
                ]


resultCount : Pagination Community -> Html Msg
resultCount pagination =
    if not <| Pagination.hasNone pagination then
        Html.div []
            [ text "Showing "
            , Html.b [] [ text <| toString <| Pagination.getTotalItems pagination ]
            , text " communities."
            ]
    else
        text ""


pagination : Pagination Community -> List (Html Msg)
pagination communityPagination =
    let
        currentPage =
            Pagination.getPage communityPagination

        backArrow =
            if Pagination.hasPrevious communityPagination then
                Html.li [ class "page-item" ]
                    [ navigateLink (Listings <| currentPage - 1) "page-link" ("«") ]
            else
                Html.li [ class "page-item disabled" ]
                    [ Html.a [ class "page-link" ] [ text "«" ] ]

        forwardArrow =
            if Pagination.hasNext communityPagination then
                Html.li [ class "page-item" ]
                    [ navigateLink (Listings <| currentPage + 1) "page-link" ("»") ]
            else
                Html.li [ class "page-item disabled" ]
                    [ Html.a [ class "page-link" ] [ text "»" ] ]

        lastPage =
            Pagination.getTotalPages communityPagination

        showMiddle =
            currentPage > 4 && currentPage < lastPage - 3

        dots =
            Html.li [ class "page-item disabled" ] [ Html.span [ class "page-link" ] [ text "..." ] ]

        splitSections =
            lastPage > 6

        firstNumbers =
            if splitSections && not showMiddle then
                List.range 1 4
                    |> List.map pageLink
            else if splitSections then
                List.range 1 2
                    |> List.map pageLink
            else
                List.range 1 8
                    |> List.map pageLink

        middleNumbers =
            if showMiddle then
                List.range (currentPage - 2) (currentPage + 2)
                    |> List.map pageLink
            else
                []

        endNumbers =
            if splitSections && not showMiddle then
                List.range (lastPage - 3) lastPage
                    |> List.map pageLink
            else if splitSections then
                List.range (lastPage - 1) lastPage
                    |> List.map pageLink
            else
                []

        pageLink page =
            if page == currentPage then
                Html.li [ class "page-item active" ]
                    [ Html.span [ class "page-link" ] [ text <| toString page ] ]
            else
                Html.li [ class "page-item" ]
                    [ navigateLink (Listings page) "page-link" (toString page) ]
    in
        List.concat
            [ [ backArrow ]
            , firstNumbers
            , [ if showMiddle then
                    dots
                else
                    text ""
              ]
            , middleNumbers
            , [ dots ]
            , endNumbers
            , [ forwardArrow ]
            ]


communityItem : Maybe Date -> Community -> Html msg
communityItem maybeCurrentDate community =
    let
        address { city, state, country } =
            [ city, state, country ]
                |> List.filter (not << String.isEmpty)
                |> String.join ", "

        imageElement name imageUrl =
            Html.div [ class "text-center text-sm-left" ]
                [ Html.img
                    [ src imageUrl
                    , alt name
                    , class "float-sm-left img-thumbnail mr-sm-2 mb-1"
                    ]
                    []
                ]

        maybeImage { name, thumbnailUrl } =
            maybeHtml (imageElement name) thumbnailUrl

        visitorsWelcomeClass welcomeStatus =
            case welcomeStatus of
                Welcome ->
                    "text-success"

                Rarely ->
                    "text-warning"

                NoVisitors ->
                    "text-danger"

        visitorsWelcome welcomeStatus =
            Html.span [ class <| visitorsWelcomeClass welcomeStatus ]
                [ text <| visitorsWelcomeToString welcomeStatus ]

        membersWelcomeClass welcomeStatus =
            case welcomeStatus of
                Yes ->
                    "text-success"

                Waitlist ->
                    "text-warning"

                NoMembers ->
                    "text-danger"

        membersWelcome welcomeStatus =
            Html.span [ class <| membersWelcomeClass welcomeStatus ]
                [ text <| membersWelcomeToString welcomeStatus ]

        timeAgo date =
            maybeCurrentDate
                |> maybeHtml
                    (\currentDate ->
                        text <|
                            " ("
                                ++ Date.Distance.inWords currentDate date
                                ++ " ago)"
                    )
    in
        Html.a
            [ class "list-group-item list-group-item-action"
            , href <| "/directory/" ++ community.slug
            ]
            [ Html.div [ class "mb-2 w-100" ]
                [ Html.div [ class "clearfix" ]
                    [ maybeImage community
                    , Html.h2 []
                        [ text community.name
                        , Html.br [] []
                        , Html.small []
                            [ text <| address community ++ " - "
                            , Html.em []
                                [ text <| statusToString community.status ]
                            ]
                        ]
                    , Html.div []
                        [ Html.div []
                            [ Html.b [] [ text "Visitors Accepted: " ]
                            , visitorsWelcome community.openToVisitors
                            ]
                        , Html.div []
                            [ Html.b []
                                [ text "Open to New Members: " ]
                            , membersWelcome community.openToMembers
                            ]
                        ]
                    , Html.div []
                        [ Html.b [] [ text "Community Types: " ]
                        , text <|
                            String.join ", " <|
                                List.map typeToString community.communityTypes
                        ]
                    ]
                ]
            , Html.div [ class "small text-muted" ]
                [ Html.b [] [ text "Updated on: " ]
                , text <| Date.Format.format "%b %e, %Y" community.updatedAt
                , timeAgo community.updatedAt
                , text " | "
                , Html.b [] [ text "Created on: " ]
                , text <| Date.Format.format "%b %e, %Y" community.createdAt
                , timeAgo community.createdAt
                ]
            ]
