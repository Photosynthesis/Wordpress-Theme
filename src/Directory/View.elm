module View exposing (view)

import Date exposing (Date)
import Date.Distance
import Date.Format
import Html exposing (Html, text)
import Html.Attributes exposing (class, src, alt, href)
import Html.Events exposing (onClick)
import Communities exposing (..)
import Pagination exposing (Pagination)
import Messages exposing (Msg(..))
import Model exposing (Model)


maybeHtml : (a -> Html msg) -> Maybe a -> Html msg
maybeHtml viewFunction =
    Maybe.map viewFunction >> Maybe.withDefault (text "")


view : Model -> Html Msg
view { communities, currentDate } =
    Html.div []
        [ links
        , resultCount communities
        , Html.div [ class "list-group directory-listings mt-2" ] <|
            List.map (communityItem currentDate) <|
                Pagination.getCurrent communities
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
        backArrow =
            if Pagination.hasPrevious communityPagination then
                Html.li [ class "page-item", onClick PreviousPage ]
                    [ Html.a [ class "page-link", href "#" ] [ text "«" ] ]
            else
                Html.li [ class "page-item disabled" ]
                    [ Html.a [ class "page-link" ] [ text "«" ] ]

        forwardArrow =
            if Pagination.hasNext communityPagination then
                Html.li [ class "page-item", onClick NextPage ]
                    [ Html.a [ class "page-link", href "#" ] [ text "»" ] ]
            else
                Html.li [ class "page-item disabled" ]
                    [ Html.a [ class "page-link" ] [ text "»" ] ]

        currentPage =
            Pagination.getPage communityPagination

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
                Html.li [ class "page-item", onClick <| JumpToPage page ]
                    [ Html.a [ class "page-link", href "#" ] [ text <| toString page ] ]
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
