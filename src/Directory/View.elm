module Directory.View exposing (view)

import Date exposing (Date)
import Date.Distance
import Date.Format
import Directory.Commands exposing (CommunitiesRequestData)
import Directory.Communities exposing (..)
import Directory.Messages exposing (Msg(..))
import Directory.Model exposing (Model)
import Directory.Pagination as Pagination exposing (Pagination)
import Directory.Routing as Routing exposing (Route(..), ListingsRoute(..), FilterParam(..), reverse)
import Html exposing (Html, text)
import Html.Attributes exposing (class, src, alt, href, name, type_, checked, height, width, value, target, id, title)
import Html.Events exposing (onClick, onInput, onSubmit, onWithOptions, defaultOptions)
import Json.Decode as Decode
import Markdown
import Regex exposing (HowMany(All), regex)
import RemoteData exposing (WebData)


{-| Render a Link to an Internal Application Page.
-}
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


{-| Return an HTML Element or an Empty Node.
-}
maybeHtml : (a -> Html msg) -> Maybe a -> Html msg
maybeHtml viewFunction =
    Maybe.map viewFunction >> Maybe.withDefault (text "")


{-| Render the Application's State.
-}
view : Model -> Html Msg
view model =
    case model.route of
        ListingsRoute listingsRoute ->
            listingsView model.communities model.searchString model.currentDate listingsRoute

        DetailsRoute _ ->
            detailsView model.currentDate model.community


{-| Render a Details Page.
-}
detailsView : Maybe Date -> WebData CommunityDetails -> Html Msg
detailsView currentDate community =
    case community of
        RemoteData.NotAsked ->
            Html.div [ class "text-danger text-center my-4" ]
                [ text "Sorry, we encountered a problem when trying to load the Community, please try again or contact "
                , Html.a [ href "mailto:directory@ic.org" ] [ text "directory@ic.org" ]
                , text "."
                ]

        RemoteData.Loading ->
            loadingBar

        RemoteData.Failure err ->
            Html.div [ class "text-danger text-center my-4" ]
                [ text "Sorry, we encountered a problem when trying to load the Community, please try again or contact "
                , Html.a [ href "mailto:directory@ic.org" ] [ text "directory@ic.org" ]
                , text "."
                , Html.p [] [ text <| toString err ]
                ]

        RemoteData.Success details ->
            communityDetails currentDate details


loadingBar : Html msg
loadingBar =
    Html.div [ class "tall" ]
        [ Html.div [ class "text-primary text-center" ] [ text "Loading..." ]
        , Html.div [ class "progress align-middle" ]
            [ Html.div [ class "progress-bar progress-bar-striped progress-bar-animated w-100" ] [] ]
        ]


communityDetails : Maybe Date -> CommunityDetails -> Html Msg
communityDetails maybeCurrentDate community =
    let
        area =
            [ community.city, community.state, community.country ]
                |> List.filter ((/=) "")
                |> String.join ", "
                |> text

        header =
            Html.div [ class "clearfix" ]
                [ Html.div [ class "float-left" ]
                    [ Html.h1 [ class "mb-1" ] [ text community.name ]
                    , Html.h2 [] [ Html.small [] [ area ] ]
                    , if community.status == Disbanded then
                        Html.strong [ class "text-danger" ] [ text "Disbanded Community" ]
                      else
                        text ""
                    ]
                , Html.ul [ class "text-right small text-muted float-right list-unstyled" ]
                    [ Html.li [] <| updatedOn maybeCurrentDate community
                    , Html.li [] <| createdOn maybeCurrentDate community
                    ]
                ]

        leftColumn =
            Html.div [ class "col-24 col-sm-14" ]
                [ Html.p [] [ text "TODO: Image, Lightbox" ]
                , Html.h2 [] [ text "Mission Statement" ]
                , Html.p [] [ text community.missionStatement ]
                , Html.h2 [] [ text "Community Description" ]
                , Markdown.toHtml [] community.description
                ]

        infoBlock header content =
            Html.div [ class "card" ]
                [ Html.h3 [ class "card-header" ] [ text header ]
                , Html.div [ class "card-block" ]
                    [ Html.ul [ class "list-unstyled pl-0" ] <|
                        List.map
                            (\( l, c ) ->
                                Html.li [ class "pb-2" ]
                                    [ Html.b [] [ text l, text ":" ]
                                    , text " "
                                    , c
                                    ]
                            )
                            content
                    ]
                ]

        infoBlockSublist =
            Html.ul [] << List.map (\c -> Html.li [] [ text c ])
    in
        Html.div [ class "directory-listing" ]
            [ header
            , Html.div [ class "row mb-2" ]
                [ leftColumn
                , detailRightColumn community
                ]
            , Html.div [ class "card-columns listing-info-blocks" ]
                [ infoBlock "About"
                    [ ( "Type(s)"
                      , infoBlockSublist <| List.map typeToString community.communityTypes
                      )
                    , ( "Programs & Activities"
                      , infoBlockSublist community.programsAndActivites
                      )
                    , ( "Location", text <| locationTypeToString community.location )
                    ]
                ]
            , text "TODO: Remaining Info Blocks & Additional Sections"
            , Html.div []
                [ Html.h3 [] [ text "Community Network or Organization Affiliations" ]
                , Html.p []
                    [ text <|
                        String.join ", " <|
                            List.filter (not << String.isEmpty) <|
                                community.networkAffiliations
                                    ++ [ community.otherAffiliations ]
                    ]
                ]
            , renderNonEmpty community.keywords <|
                \keywords ->
                    Html.div []
                        [ Html.h3 [] [ text "Keywords" ]
                        , Html.p [] [ text keywords ]
                        ]
            ]


renderNonEmpty : String -> (String -> Html msg) -> Html msg
renderNonEmpty value renderer =
    if value == "" then
        text ""
    else
        renderer value


detailRightColumn : CommunityDetails -> Html msg
detailRightColumn community =
    let
        rightColumn =
            [ boldLabel "Status" <| renderStatus community.status
            , boldLabelText "Started Planning" <| toString community.startedPlanning
            , boldLabelText "Started Living Together" <| toString community.startedLivingTogether
            , boldLabel "Visitors Accepted" <| visitorsWelcome community.openToVisitors
            , boldLabel "Open to New Members" <| membersWelcome community.openToMembers
            , Html.li [ class "text-center mt-2 mb-3" ]
                [ Html.small [ class "text-muted" ]
                    [ text "Please read the details in "
                    , Html.a [ href "#Membership" ] [ text "Membership" ]
                    , text " below before contacting this community."
                    ]
                , Html.a
                    [ class "my-1 btn btn-block btn-warning"
                    , href <| "/directory/contact-a-community/?cmty=" ++ (\(CommunityID i) -> toString i) community.id
                    ]
                    [ text "Send Message"
                    ]
                ]
            , urlItem "Website" community.websiteUrl
            , urlItem "Business, Project, or Organization" community.businessUrl
            , urlItem "Facebook" community.facebookUrl
            , urlItem "Twitter" community.twitterUrl
            , urlItem "Other Social" community.socialUrl
            , renderNonEmpty community.contactName <| boldLabelText "Contact Name"
            , renderNonEmpty community.contactPhone <| boldLabelText "Phone"
            , renderJust community.contactAddress renderAddress
            , htmlIf community.isFicMember <|
                ficBadge community.ficMembershipStartYear
            , renderJust community.disbandedInfo <|
                extraStatusInfo "This Community Has Disbanded" "Year Disbanded"
            , renderJust community.reformingInfo <|
                extraStatusInfo "This Community Is Re-Forming" "Year Re-Formed"
            , Html.li [] [ text "TODO: Google Map" ]
            ]

        htmlIf condition html =
            if condition then
                html
            else
                text ""

        urlItem name value =
            renderNonEmpty value <| boldLabel name << textLink

        renderAddress address =
            Html.li []
                [ Html.b [] [ text <| addressTypeToString address.addressType ++ ":" ]
                , Html.address [] <|
                    List.intersperse (Html.br [] []) <|
                        List.map text <|
                            List.filter ((/=) "")
                                [ address.lineOne
                                , address.lineTwo
                                , (\s -> s ++ " " ++ address.zipCode) <|
                                    String.join ", " <|
                                        List.filter ((/=) "") <|
                                            [ community.city, community.state ]
                                , community.country
                                ]
                ]

        ficBadge startYear =
            Html.li []
                [ Html.h3 [ class "text-center" ]
                    [ Html.a [ href "/community-bookstore/product/fic-membership/", target "_blank" ]
                        [ text "FIC Membership" ]
                    ]
                , Html.div [ class "text-center mb-3" ]
                    [ renderNonEmpty startYear <| \year -> Html.small [] [ text <| "Since " ++ year ]
                    , Html.a [ href "/community-bookstore/product/fic-membership/", target "_blank" ]
                        [ Html.img
                            [ id "fic-membership-badge"
                            , title "FIC Membership Badge"
                            , src "/wp-content/images/fic-membership-badge.png"
                            , alt "This Community is an FIC Member"
                            ]
                            []
                        ]
                    ]
                ]

        extraStatusInfo title label info =
            Html.li []
                [ Html.div []
                    [ Html.h3 [ class "text-center" ] [ text title ]
                    , renderNonEmpty info.year <| boldLabelText label
                    , renderNonEmpty info.info <| \i -> Html.p [] [ text i ]
                    ]
                ]

        renderJust value renderer =
            Maybe.map renderer value |> Maybe.withDefault (text "")

        textLink url =
            Html.a [ href url, target "_blank" ] [ text url ]

        boldLabel label value =
            Html.li [] [ Html.b [] [ text <| label ++ ":" ], text " ", value ]

        boldLabelText label value =
            boldLabel label <| text value
    in
        Html.div [ class "col-24 col-sm-10" ]
            [ Html.div [ class "card" ]
                [ Html.ul [ class "card-block list-unstyled listing-status" ] rightColumn
                ]
            ]


renderStatus : CommunityStatus -> Html msg
renderStatus status =
    let
        statusClass =
            if status == Disbanded then
                "text-danger"
            else
                ""
    in
        Html.span [ class statusClass ] [ text <| statusToString status ]


visitorsWelcome : VisitorsWelcome -> Html msg
visitorsWelcome welcomeStatus =
    let
        visitorsWelcomeClass welcomeStatus =
            case welcomeStatus of
                Welcome ->
                    "text-success"

                Rarely ->
                    "text-warning"

                NoVisitors ->
                    "text-danger"
    in
        Html.span [ class <| visitorsWelcomeClass welcomeStatus ]
            [ text <| visitorsWelcomeToString welcomeStatus ]


membersWelcome : MembersWelcome -> Html msg
membersWelcome welcomeStatus =
    let
        membersWelcomeClass welcomeStatus =
            case welcomeStatus of
                Yes ->
                    "text-success"

                Waitlist ->
                    "text-warning"

                NoMembers ->
                    "text-danger"
    in
        Html.span [ class <| membersWelcomeClass welcomeStatus ]
            [ text <| membersWelcomeToString welcomeStatus ]


{-| Return the text to display for a Listing's Updated Date.
-}
updatedOn : Maybe Date -> { a | updatedAt : Date } -> List (Html msg)
updatedOn currentDate community =
    [ Html.b [] [ text "Updated on: " ]
    , text <| Date.Format.format "%b %e, %Y" community.updatedAt
    , timeAgo currentDate community.updatedAt
    ]


createdOn : Maybe Date -> { a | createdAt : Date } -> List (Html msg)
createdOn currentDate community =
    [ Html.b [] [ text "Created on: " ]
    , text <| Date.Format.format "%b %e, %Y" community.createdAt
    , timeAgo currentDate community.createdAt
    ]


{-| Return text saying how long ago a date was. Or nothing if we do not have
the current date.
-}
timeAgo : Maybe Date -> Date -> Html msg
timeAgo maybeCurrentDate date =
    maybeCurrentDate
        |> maybeHtml
            (\currentDate ->
                text <| " (" ++ Date.Distance.inWords currentDate date ++ " ago)"
            )


{-| Render a Listings Page.
-}
listingsView :
    Pagination CommunityListing CommunitiesRequestData
    -> String
    -> Maybe Date
    -> ListingsRoute
    -> Html Msg
listingsView communities searchString currentDate route =
    let
        maybeRssLink =
            case route of
                RecentlyAdded _ _ ->
                    Just "/rss-newly-listed-directory-listings/"

                RecentlyUpdated _ _ ->
                    Just "/rss-recently-updated-community-listings/"

                _ ->
                    Nothing

        rssIcon =
            Maybe.map
                (\link ->
                    Html.a [ href link, class "float-right" ]
                        [ Html.img
                            [ src "/wp-content/uploads/2014/01/RSS_icon.png"
                            , width 32
                            , height 32
                            , alt "RSS Feed"
                            ]
                            []
                        ]
                )
                >> Maybe.withDefault (text "")

        pageHeading =
            Html.div [ class "clearfix directory-rss" ]
                [ rssIcon maybeRssLink
                , Html.h1 [ class "page-title" ] [ text <| Routing.getPageTitle route ]
                ]

        listings =
            Html.div []
                [ searchForm
                , links
                , Html.div [ class "clearfix" ]
                    [ if not <| List.isEmpty (Pagination.getCurrent communities) then
                        resultCount communities
                      else
                        text ""
                    , filterHtml route
                    ]
                , communitiesList
                , if not <| List.isEmpty (Pagination.getCurrent communities) then
                    Html.div []
                        [ links
                        , Html.ul [ class "pagination justify-content-center" ] <|
                            pagination route communities
                        ]
                  else
                    text ""
                ]

        searchForm =
            Html.form [ class "justify-content-center form-inline", onSubmit SubmitSearchForm ]
                [ Html.input
                    [ class "d-inline-block mr-2 form-control"
                    , value searchString
                    , onInput UpdateSearchString
                    , name "search"
                    ]
                    []
                , Html.input
                    [ class "btn btn-primary"
                    , type_ "submit"
                    , value "Search"
                    ]
                    []
                ]

        communitiesList =
            if Pagination.isLoading communities then
                loadingBar
            else if Pagination.getError communities /= Nothing then
                Html.div [ class "text-danger text-center my-4" ]
                    [ text "Sorry, we encountered a problem when trying to load Communities, please try again or contact "
                    , Html.a [ href "mailto:directory@ic.org" ] [ text "directory@ic.org" ]
                    , text "."
                    ]
            else if Pagination.hasNone communities then
                Html.div [ class "tall text-danger text-center" ]
                    [ text "Sorry, we couldn't find any matching Communities." ]
            else
                Html.div [ class "list-group directory-listings mt-2" ] <|
                    List.map (communityItem currentDate) <|
                        Pagination.getCurrent communities
    in
        Html.div []
            [ pageHeading
            , listings
            ]


{-| Render the Links Appearing Above & Below the Listings.
-}
links : Html Msg
links =
    let
        staticLinks =
            List.map
                (\( content, slug ) ->
                    Html.a [ href <| "/directory/" ++ slug ] [ text content ]
                )
                [ ( "Types", "community-types" )
                , ( "State/Country List", "intentional-communities-by-country" )
                , ( "Maps", "map" )
                , ( "Advanced Search", "search" )
                ]

        pageLinks =
            List.map
                (\( content, route ) ->
                    navigateLink (ListingsRoute <| route 1 []) "" content
                )
                [ ( "Newest Communities", RecentlyAdded )
                , ( "Recently Updated", RecentlyUpdated )
                ]
    in
        Html.div [ class "directory-header-links" ] <|
            List.intersperse (text " | ") (pageLinks ++ staticLinks)


{-| Render the Result Count if there are Results.
-}
resultCount : Pagination CommunityListing CommunitiesRequestData -> Html Msg
resultCount pagination =
    if not <| Pagination.hasNone pagination then
        Html.div [ class "float-left" ]
            [ text "Showing "
            , Html.b [] [ text <| toString <| Pagination.getTotalItems pagination ]
            , text " communities."
            ]
    else
        text ""


{-| Render the Filter Checkbox Inputes.
-}
filterHtml : ListingsRoute -> Html Msg
filterHtml route =
    let
        currentFilters =
            List.foldl
                (\inherentFilter extraFilters ->
                    List.filter (\f -> f /= inherentFilter) extraFilters
                )
                (Routing.getAdditionalFilters route)
                (Routing.getInherentFilters route)

        annotate filter =
            (\( n, t ) -> ( filter, n, t, List.member filter currentFilters )) <|
                case filter of
                    VisitorsFilter ->
                        ( "visitors", "Visitors Welcome" )

                    MembersFilter ->
                        ( "members", "Accepting Members" )

                    EstablishedFilter ->
                        ( "established", "Established" )

                    FormingFilter ->
                        ( "forming", "Forming" )

                    FICMemberFilter ->
                        ( "fic-member", "FIC Member" )

                    _ ->
                        Debug.crash "Unhandled Inline Filter in `filterHtml`"

        checkMsg filter isOn =
            NavigateTo
                << ListingsRoute
                << Routing.toPageOne route
            <|
                if isOn then
                    List.filter (\f -> f /= filter) currentFilters
                else
                    filter :: currentFilters

        render ( filter, filterName, filterText, isOn ) =
            Html.label [ onClick <| checkMsg filter isOn ]
                [ Html.input
                    [ type_ "checkbox"
                    , name filterName
                    , checked isOn
                    ]
                    []
                , Html.span [] [ text <| " " ++ filterText ]
                ]
    in
        Html.div [ class "float-right directory-filters" ] <|
            List.map (annotate >> render) Routing.inlineFilters


{-| Render the Pagination for the Listings.
-}
pagination : ListingsRoute -> Pagination CommunityListing CommunitiesRequestData -> List (Html Msg)
pagination route communityPagination =
    let
        currentPage =
            Pagination.getPage communityPagination

        backArrow =
            if Pagination.hasPrevious communityPagination then
                Html.li [ class "page-item" ]
                    [ navigateLink (ListingsRoute <| Routing.mapPage (always <| currentPage - 1) route)
                        "page-link"
                        ("«")
                    ]
            else
                Html.li [ class "page-item disabled" ]
                    [ Html.a [ class "page-link" ] [ text "«" ] ]

        forwardArrow =
            if Pagination.hasNext communityPagination then
                Html.li [ class "page-item" ]
                    [ navigateLink (ListingsRoute <| Routing.mapPage (always <| currentPage + 1) route)
                        "page-link"
                        ("»")
                    ]
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
            lastPage > 8

        firstNumbers =
            if splitSections && not showMiddle then
                List.range 1 4
                    |> List.map pageLink
            else if splitSections then
                List.range 1 2
                    |> List.map pageLink
            else
                List.range 1 lastPage
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
                    [ navigateLink (ListingsRoute <| Routing.mapPage (always page) route) "page-link" (toString page) ]
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
            , [ if splitSections then
                    dots
                else
                    text ""
              ]
            , endNumbers
            , [ forwardArrow ]
            ]


{-| Render a Single Community in the Listings.
-}
communityItem : Maybe Date -> CommunityListing -> Html msg
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
    in
        Html.a
            [ class "list-group-item list-group-item-action"
            , href <| "/directory/" ++ community.slug
            ]
            [ Html.div [ class "mb-2 w-100" ]
                [ Html.div [ class "clearfix" ]
                    [ maybeImage community
                    , Html.h2 []
                        [ text <| Regex.replace All (regex "&amp;") (always "&") community.name
                        , Html.br [] []
                        , Html.small []
                            [ text <| address community ++ " - "
                            , Html.em []
                                [ renderStatus community.status ]
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
            , Html.div [ class "small text-muted" ] <|
                updatedOn maybeCurrentDate community
                    ++ [ text " | " ]
                    ++ createdOn maybeCurrentDate community
            ]
